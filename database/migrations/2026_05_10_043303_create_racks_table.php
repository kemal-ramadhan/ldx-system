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
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->integer('total_units')->default(0);
            $table->integer('power_capacity')->default(0);
            $table->integer('weight_capacity')->default(0);
            $table->string('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'full'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racks');
    }
};
