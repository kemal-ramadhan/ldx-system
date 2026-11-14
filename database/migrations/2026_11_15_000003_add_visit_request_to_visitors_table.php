<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link a guest-book entry to the visit request it came from (if any).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->foreignId('visit_request_id')->nullable()->after('status')
                ->constrained('visit_requests')->nullOnDelete();
            $table->foreignId('visit_request_member_id')->nullable()->after('visit_request_id')
                ->constrained('visit_request_members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visit_request_member_id');
            $table->dropConstrainedForeignId('visit_request_id');
        });
    }
};
