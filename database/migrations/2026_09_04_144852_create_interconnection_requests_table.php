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
        Schema::create('interconnection_requests', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Request Information
            |--------------------------------------------------------------------------
            */

            $table->string('request_number', 50)->unique();

            $table->foreignId('requester_client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Connection Endpoints
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
            | Connection Specification
            |--------------------------------------------------------------------------
            */

            $table->enum('interconnection_type', [
                'internal_building',
                'cross_connect',
                'other',
            ])->default('internal_building');

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
            | Request Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'submitted',
                'waiting_destination_approval',
                'waiting_dc_approval',
                'approved',
                'rejected',
                'in_progress',
                'testing',
                'completed',
                'cancelled',
            ])->default('draft');

            $table->unsignedTinyInteger('progress')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->foreignId('destination_approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('destination_approved_at')->nullable();

            $table->foreignId('dc_approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('dc_approved_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Completion
            |--------------------------------------------------------------------------
            */

            $table->timestamp('requested_at')->nullable();

            $table->timestamp('started_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Additional Information
            |--------------------------------------------------------------------------
            */

            $table->text('description')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('requester_client_id');
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
        Schema::dropIfExists('interconnection_requests');
    }
};
