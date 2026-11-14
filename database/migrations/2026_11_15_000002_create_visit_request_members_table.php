<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * People joining a visit request. Same data as the public guest book, so on
 * the visit day they only confirm it at check-in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_request_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_request_id')->constrained('visit_requests')->cascadeOnDelete();

            $table->string('name');
            $table->string('nik', 20);
            $table->string('phone', 20);
            $table->string('email');
            $table->string('address');
            $table->string('company_name');
            $table->string('position_in_company');

            $table->foreignId('visitor_id')->nullable()->constrained('visitors')->nullOnDelete();
            $table->dateTime('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('checked_out_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_request_members');
    }
};
