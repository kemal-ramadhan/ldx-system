<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Device placement for the check-in / check-out (keluar-masuk) feature.
 *  - placement_status: in_rack | out_temporary | removed
 *  - start_unit / end_unit: were sent on create but never stored; backfilled from rack_units
 *  - removed_at: when the device permanently left the data center
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rack_divices', function (Blueprint $table) {
            $table->enum('placement_status', ['in_rack', 'out_temporary', 'removed'])
                ->default('in_rack')->after('status');
            $table->unsignedSmallInteger('start_unit')->nullable()->after('total_unit');
            $table->unsignedSmallInteger('end_unit')->nullable()->after('start_unit');
            $table->dateTime('removed_at')->nullable()->after('installed_at');

            $table->index('placement_status');
        });

        // backfill U position from the units each device occupies
        $positions = DB::table('rack_units')
            ->whereNotNull('rack_divice_id')
            ->whereNull('deleted_at')
            ->groupBy('rack_divice_id')
            ->select('rack_divice_id', DB::raw('MIN(unit_number) as start_unit'), DB::raw('MAX(unit_number) as end_unit'))
            ->get();

        foreach ($positions as $position) {
            DB::table('rack_divices')
                ->where('id', $position->rack_divice_id)
                ->update([
                    'start_unit' => $position->start_unit,
                    'end_unit' => $position->end_unit,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('rack_divices', function (Blueprint $table) {
            $table->dropIndex(['placement_status']);
            $table->dropColumn(['placement_status', 'start_unit', 'end_unit', 'removed_at']);
        });
    }
};
