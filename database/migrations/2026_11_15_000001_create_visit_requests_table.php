<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengajuan kunjungan from the client portal.
 * Visitors are only written to `visitors` when they actually check in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();

            $table->date('visit_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('type_of_visit', 50);
            $table->string('purpose', 255);
            $table->text('notes')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->string('checkin_code', 12)->nullable()->unique();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'visit_date']);
        });

        Schema::create('visit_request_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_request_id')->constrained('visit_requests')->cascadeOnDelete();
            $table->foreignId('rack_id')->constrained('racks')->cascadeOnDelete();
            $table->unique(['visit_request_id', 'rack_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_request_racks');
        Schema::dropIfExists('visit_requests');
    }
};
