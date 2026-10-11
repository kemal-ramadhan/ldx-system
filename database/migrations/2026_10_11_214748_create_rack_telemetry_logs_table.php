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
        Schema::create('rack_telemetry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->nullable()->constrained('racks')->nullOnDelete();
            $table->string('api_rack_name', 50)->index(); // identifier e.g. "4A-2"
            $table->unsignedInteger('slave')->nullable();
            $table->string('status', 30)->default('normal'); // normal, warning, offline

            // Suhu & Kelembapan
            $table->decimal('temp', 5, 2)->nullable();
            $table->decimal('hum', 5, 2)->nullable();
            $table->string('status_temp', 30)->nullable();
            $table->string('status_hum', 30)->nullable();

            // PLN Feed
            $table->boolean('pln_ada')->default(false);
            $table->decimal('pln_voltage', 6, 2)->nullable();
            $table->decimal('pln_current', 7, 3)->nullable();
            $table->decimal('pln_power', 8, 2)->nullable();
            $table->string('pln_status', 30)->nullable();

            // UPS Feed
            $table->boolean('ups_ada')->default(false);
            $table->decimal('ups_voltage', 6, 2)->nullable();
            $table->decimal('ups_current', 7, 3)->nullable();
            $table->decimal('ups_power', 8, 2)->nullable();
            $table->string('ups_status', 30)->nullable();

            // Sensor reading timestamp
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            // Compound index for fast queries by rack and time range
            $table->index(['rack_id', 'recorded_at']);
            $table->index(['api_rack_name', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rack_telemetry_logs');
    }
};
