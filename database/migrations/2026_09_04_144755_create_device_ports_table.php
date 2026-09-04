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
        Schema::create('device_ports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rack_divice_id')
                ->constrained('rack_divices')
                ->cascadeOnDelete();

            $table->string('port_name', 100);
            $table->string('port_number', 50)->nullable();

            $table->enum('port_type', [
                'ethernet',
                'fiber',
                'management',
                'power',
                'console',
                'other',
            ])->default('ethernet');

            $table->string('connector_type', 50)->nullable();

            $table->enum('status', [
                'available',
                'connected',
                'disabled',
                'maintenance',
            ])->default('available');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique([
                'rack_divice_id',
                'port_name',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_ports');
    }
};
