<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Some money columns were DECIMAL(10,2) (max ~Rp 99.999.999,99) while the
 * rest of the billing data is DECIMAL(15,2). Widen them so every nominal keeps
 * 2 decimals (cents) and large invoices fit. Existing values are unchanged.
 */
return new class extends Migration
{
    private array $columns = [
        'invoices' => ['subtotal', 'total'],
        'invoice_items' => ['price', 'total'],
        'payments' => ['amount'],
        'reculling_billings' => ['amount'],
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column) {
                    $blueprint->decimal($column, 15, 2)->default(0)->change();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column) {
                    $blueprint->decimal($column, 10, 2)->default(0)->change();
                }
            });
        }
    }
};
