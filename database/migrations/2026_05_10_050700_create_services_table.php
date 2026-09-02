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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('rack_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'annually', 'one_time', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->boolean('ppn_enabled')->default(false)->nullable();
            $table->decimal('ppn_percentage', 5, 2)->default(0)->nullable();
            $table->decimal('ppn_amount', 15, 2)->default(0)->nullable();
            $table->boolean('pph23_enabled')->default(false)->nullable();
            $table->decimal('pph23_percentage', 5, 2)->default(0)->nullable();
            $table->decimal('pph23_amount', 15, 2)->default(0)->nullable();
            $table->decimal('monthly_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended', 'terminated'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
