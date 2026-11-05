<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tax (PPN / PPh 23) moves from the service/invoice header to each item.
 * Header columns stay as the sum of the items.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['service_items', 'invoice_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->boolean('ppn_enabled')->default(false);
                $table->decimal('ppn_percentage', 5, 2)->default(0);
                $table->decimal('ppn_amount', 15, 2)->default(0);
                $table->boolean('pph23_enabled')->default(false);
                $table->decimal('pph23_percentage', 5, 2)->default(0);
                $table->decimal('pph23_amount', 15, 2)->default(0);
            });
        }

        /**
         * =========================================
         * BACKFILL: copy header tax settings to items
         * =========================================
         */
        $this->backfill('service_items', 'services', 'service_id', 'subtotal');
        $this->backfill('invoice_items', 'invoices', 'invoice_id', 'total');
    }

    private function backfill(string $itemTable, string $parentTable, string $foreignKey, string $amountColumn): void
    {
        DB::table($parentTable)
            ->where(function ($q) {
                $q->where('ppn_enabled', true)->orWhere('pph23_enabled', true);
            })
            ->orderBy('id')
            ->chunkById(200, function ($parents) use ($itemTable, $foreignKey, $amountColumn) {
                foreach ($parents as $parent) {
                    $ppnEnabled = (bool) $parent->ppn_enabled;
                    $ppnPct = $ppnEnabled ? (float) $parent->ppn_percentage : 0;
                    $pphEnabled = (bool) $parent->pph23_enabled;
                    $pphPct = $pphEnabled ? (float) $parent->pph23_percentage : 0;

                    DB::table($itemTable)
                        ->where($foreignKey, $parent->id)
                        ->update([
                            'ppn_enabled' => $ppnEnabled,
                            'ppn_percentage' => $ppnPct,
                            'ppn_amount' => DB::raw("ROUND({$amountColumn} * {$ppnPct} / 100, 2)"),
                            'pph23_enabled' => $pphEnabled,
                            'pph23_percentage' => $pphPct,
                            'pph23_amount' => DB::raw("ROUND({$amountColumn} * {$pphPct} / 100, 2)"),
                        ]);
                }
            });
    }

    public function down(): void
    {
        foreach (['service_items', 'invoice_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn([
                    'ppn_enabled',
                    'ppn_percentage',
                    'ppn_amount',
                    'pph23_enabled',
                    'pph23_percentage',
                    'pph23_amount',
                ]);
            });
        }
    }
};
