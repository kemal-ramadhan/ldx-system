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
        Schema::create('request_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_divice_id')->constrained()->onDelete('cascade');
            $table->foreignId('rack_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->unique()->nullable();
            $table->enum('request_type', ['add', 'remove'])->default('add');
            $table->integer('total_unit')->default(1);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('notes')->nullable();
            $table->enum('approval_status_admin', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('approval_status_technician', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by_admin')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_technician')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at_admin')->nullable();
            $table->timestamp('approved_at_technician')->nullable();
            $table->date('requested_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'installed', 'success', 'pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_devices');
    }
};
