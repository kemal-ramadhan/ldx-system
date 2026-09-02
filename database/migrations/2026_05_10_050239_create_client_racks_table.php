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
        Schema::create('client_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('rack_id')->constrained()->onDelete('cascade');
            $table->integer('rented_units')->default(0);
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->date('rental_start_date');
            $table->date('rental_end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_racks');
    }
};
