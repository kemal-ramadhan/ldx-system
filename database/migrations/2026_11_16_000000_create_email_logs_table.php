<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every e-mail the application sends (SMTP does not keep a copy in "Sent").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();

            $table->string('mailable')->nullable();
            $table->string('type', 40)->default('other');
            $table->json('context')->nullable();

            $table->nullableMorphs('related');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();

            $table->string('subject')->nullable();
            $table->string('from')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();

            $table->longText('html_body')->nullable();
            $table->json('attachments')->nullable();

            $table->enum('status', ['sending', 'sent', 'failed'])->default('sending');
            $table->text('error_message')->nullable();
            $table->string('message_id')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('failed_at')->nullable();

            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 20)->default('web');
            $table->foreignId('resent_from_id')->nullable()->constrained('email_logs')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
