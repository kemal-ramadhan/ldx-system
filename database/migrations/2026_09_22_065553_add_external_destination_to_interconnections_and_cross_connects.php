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
            $table->enum('destination_type', ['internal', 'external'])->default('internal')->after('source_port_id');
            $table->foreignId('destination_port_id')->nullable()->change();
            
            $table->string('external_client_name')->nullable()->after('destination_port_id');
            $table->string('external_rack_name')->nullable()->after('external_client_name');
            $table->string('external_device_name')->nullable()->after('external_rack_name');
            $table->string('external_port_name')->nullable()->after('external_device_name');
        });

        Schema::table('cross_connects', function (Blueprint $table) {
            $table->enum('destination_type', ['internal', 'external'])->default('internal')->after('source_port_id');
            $table->foreignId('destination_port_id')->nullable()->change();
            
            $table->string('external_client_name')->nullable()->after('destination_port_id');
            $table->string('external_rack_name')->nullable()->after('external_client_name');
            $table->string('external_device_name')->nullable()->after('external_rack_name');
            $table->string('external_port_name')->nullable()->after('external_device_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interconnection_requests', function (Blueprint $table) {
            $table->dropColumn([
                'destination_type',
                'external_client_name',
                'external_rack_name',
                'external_device_name',
                'external_port_name'
            ]);
            // Cannot easily revert nullable change here without doctrine/dbal sometimes, so omitting it.
        });

        Schema::table('cross_connects', function (Blueprint $table) {
            $table->dropColumn([
                'destination_type',
                'external_client_name',
                'external_rack_name',
                'external_device_name',
                'external_port_name'
            ]);
        });
    }
};
