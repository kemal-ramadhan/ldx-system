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
        Schema::table('interconnection_requests', function (Blueprint $table) {
            $table->string('external_location', 20)->nullable()->after('destination_type');
        });

        Schema::table('cross_connects', function (Blueprint $table) {
            $table->string('external_location', 20)->nullable()->after('destination_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interconnection_requests', function (Blueprint $table) {
            $table->dropColumn('external_location');
        });

        Schema::table('cross_connects', function (Blueprint $table) {
            $table->dropColumn('external_location');
        });
    }
};
