<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clients can ask the LDX team to terminate an active cross connect.
 * The request is stored on the cross connect; admin performs the terminate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cross_connects', function (Blueprint $table) {
            $table->dateTime('terminate_requested_at')->nullable()->after('terminated_at');
            $table->foreignId('terminate_requested_by')->nullable()->after('terminate_requested_at')
                ->constrained('users')->nullOnDelete();
            $table->text('terminate_request_reason')->nullable()->after('terminate_requested_by');
        });
    }

    public function down(): void
    {
        Schema::table('cross_connects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('terminate_requested_by');
            $table->dropColumn(['terminate_requested_at', 'terminate_request_reason']);
        });
    }
};
