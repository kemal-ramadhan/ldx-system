<?php

namespace App\Console\Commands;

use App\Http\Controllers\InvoiceController;
use App\Models\Service;
use App\Services\ServiceInvoiceGenerator;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    /**
     * =========================================
     * COMMAND
     * =========================================
     */
    protected $signature =
        'invoices:generate';

    /**
     * =========================================
     * DESCRIPTION
     * =========================================
     */
    protected $description =
        'Generate recurring invoices for active services 7 days before their next due date';

    /**
     * Days before next_due_date the invoice is issued & emailed
     */
    private const DAYS_BEFORE_DUE = 7;

    /**
     * =========================================
     * HANDLE
     * =========================================
     */
    public function handle(): void
    {
        $this->info('Starting recurring invoice generation...');

        /**
         * =========================================
         * ACTIVE SERVICES DUE WITHIN 7 DAYS
         * =========================================
         */
        $services = Service::with(['client', 'serviceItems.product'])
            ->where('status', 'active')
            ->whereDate(
                'next_due_date',
                '<=',
                now()->addDays(self::DAYS_BEFORE_DUE)->toDateString()
            )
            ->get();

        if ($services->isEmpty()) {
            $this->warn('No services due.');
            return;
        }

        foreach ($services as $service) {

            /**
             * Skip when this period was already invoiced
             * (e.g. generated manually from the service page)
             */
            if (ServiceInvoiceGenerator::periodInvoiceExists($service)) {
                $this->warn("Invoice already exists for service {$service->code} ({$service->next_due_date}).");
                continue;
            }

            try {
                $invoice = ServiceInvoiceGenerator::generate($service);
            } catch (\Throwable $th) {
                $this->error("Failed to generate invoice for service {$service->code}: {$th->getMessage()}");
                continue;
            }

            /**
             * =========================================
             * SEND INVOICE EMAIL (H-7 notice)
             * =========================================
             */
            try {
                InvoiceController::deliverInvoiceEmail($invoice);

                $this->info("Invoice created & sent: {$invoice->invoice_number}");
            } catch (\Throwable $th) {
                $invoice->update(['status' => 'pending']);

                $this->error("Invoice {$invoice->invoice_number} created, but email failed: {$th->getMessage()}");
            }
        }

        $this->info('Recurring invoice generation completed.');
    }
}
