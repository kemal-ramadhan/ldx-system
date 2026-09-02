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
        Schema::create('rack_divices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->unique()->nullable();
            $table->string('divice_name');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('divice_type')->nullable();
            $table->string('description')->nullable();
            $table->integer('power_usage')->nullable();
            $table->integer('weight_usage')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('total_unit')->default(1);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'request'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rack_divices');
    }
};
