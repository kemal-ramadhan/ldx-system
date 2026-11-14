<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keluar-masuk device: one row per physical movement, with a snapshot of the
 * device at that moment so the history stays correct after later edits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number')->unique();

            // history must survive: never cascade-delete movements
            $table->foreignId('rack_divice_id')->constrained('rack_divices')->restrictOnDelete();

            $table->enum('type', ['check_in', 'check_out', 'temp_out', 'return_in', 'relocate']);
            $table->dateTime('occurred_at');

            // snapshot
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('device_name');
            $table->string('device_code')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();

            // from -> to
            $table->foreignId('from_rack_id')->nullable()->constrained('racks')->nullOnDelete();
            $table->unsignedSmallInteger('from_start_unit')->nullable();
            $table->unsignedSmallInteger('from_end_unit')->nullable();
            $table->foreignId('to_rack_id')->nullable()->constrained('racks')->nullOnDelete();
            $table->unsignedSmallInteger('to_start_unit')->nullable();
            $table->unsignedSmallInteger('to_end_unit')->nullable();

            $table->boolean('units_reserved')->default(false);

            // who carried the goods
            $table->string('carrier_name')->nullable();
            $table->string('carrier_company')->nullable();
            $table->string('carrier_id_number', 50)->nullable();
            $table->string('carrier_phone', 30)->nullable();
            $table->foreignId('visitor_id')->nullable()->constrained('visitors')->nullOnDelete();

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['type', 'occurred_at']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_movements');
    }
};
