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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->string('nik');
            $table->string('company_name');
            $table->string('position_in_company');
            $table->string('type_of_visit');
            $table->string('visit_purpose');
            $table->string('visit_note')->nullable();
            $table->date('visit_date');
            $table->dateTime('checkin_time');
            $table->dateTime('checkout_time')->nullable();
            $table->enum('status', ['checkin', 'checkout'])->default('checkin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
