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
        Schema::create('rack_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('rack_divice_id')->nullable();
            $table->string('code')->unique()->nullable();
            $table->integer('unit_number');
            $table->enum('status', ['empty', 'used', 'maintenance', 'reserved'])->default('empty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rack_units');
    }
};
