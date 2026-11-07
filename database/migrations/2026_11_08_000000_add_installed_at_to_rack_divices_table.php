<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tanggal & jam device dipasang di rack.
 * Existing devices get their created_at (the date they were input).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rack_divices', function (Blueprint $table) {
            $table->dateTime('installed_at')->nullable()->after('status');
        });

        // backfill: devices that already exist use their input date
        DB::table('rack_divices')
            ->whereNull('installed_at')
            ->update(['installed_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('rack_divices', function (Blueprint $table) {
            $table->dropColumn('installed_at');
        });
    }
};
