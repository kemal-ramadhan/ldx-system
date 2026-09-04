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
        Schema::create('cross_connects', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Cross Connect Information
            |--------------------------------------------------------------------------
            */

            $table->string('cross_connect_number', 50)->unique();

            $table->foreignId('interconnection_request_id')
                ->nullable()
                ->constrained('interconnection_requests')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Endpoints
            |--------------------------------------------------------------------------
            */

            $table->foreignId('source_port_id')
                ->constrained('device_ports')
                ->restrictOnDelete();

            $table->foreignId('destination_port_id')
                ->constrained('device_ports')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Cable Specification
            |--------------------------------------------------------------------------
            */

            $table->enum('cable_type', [
                'fiber_optic',
                'copper',
                'coaxial',
                'other',
            ])->default('fiber_optic');

            $table->string('connector_type', 100)->nullable();

            $table->decimal('cable_length', 10, 2)->nullable();

            $table->string('cable_length_unit', 20)->default('meter');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'planned',
                'active',
                'terminated',
                'cancelled',
            ])->default('planned');

            /*
            |--------------------------------------------------------------------------
            | Installation
            |--------------------------------------------------------------------------
            */

            $table->foreignId('installed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('installed_at')->nullable();

            $table->timestamp('terminated_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Additional Information
            |--------------------------------------------------------------------------
            */

            $table->text('description')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('interconnection_request_id');
            $table->index('source_port_id');
            $table->index('destination_port_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cross_connects');
    }
};
