<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rack_divices', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('device_ports', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_ports', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('rack_divices', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
