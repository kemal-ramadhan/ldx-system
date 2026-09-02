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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->boolean('ppn_enabled')->default(false)->nullable();
            $table->decimal('ppn_percentage', 5, 2)->default(0)->nullable();
            $table->decimal('ppn_amount', 15, 2)->default(0)->nullable();
            $table->boolean('pph23_enabled')->default(false)->nullable();
            $table->decimal('pph23_percentage', 5, 2)->default(0)->nullable();
            $table->decimal('pph23_amount', 15, 2)->default(0)->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'sent', 'waiting', 'paid', 'overdue', 'cancelled', 'processed', 'rejected'])->default('draft');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
