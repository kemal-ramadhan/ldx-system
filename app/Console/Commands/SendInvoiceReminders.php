<?php

namespace App\Console\Commands;

use App\Mail\InvoiceReminderMail;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminders extends Command
{
    /**
     * =========================================
     * COMMAND
     * =========================================
     */
    protected $signature =
        'invoices:reminders';

    /**
     * =========================================
     * DESCRIPTION
     * =========================================
     */
    protected $description =
        'Send invoice payment reminders';

    /**
     * =========================================
     * HANDLE
     * =========================================
     */
    public function handle(): void
    {

        $this->info(
            'Starting invoice reminders...'
        );

        /**
         * =========================================
         * GET PENDING INVOICES
         * =========================================
         */
        $invoices = Invoice::with([
                'client',
                'service',
            ])
            ->whereIn('status', [
                'pending',
                'sent',
            ])
            ->whereDate(
                'due_date',
                '<=',
                now()->addDays(3)
            )
            ->get();

        /**
         * =========================================
         * NO INVOICES
         * =========================================
         */
        if ($invoices->count() === 0) {

            $this->warn(
                'No invoices need reminders.'
            );

            return;
        }

        /**
         * =========================================
         * LOOP INVOICES
         * =========================================
         */
        foreach ($invoices as $invoice) {

            /**
             * =========================================
             * CHECK EMAIL
             * =========================================
             */
            if (
                !$invoice->client?->company_email
            ) {

                $this->warn(
                    'Client email not found for invoice: '
                    . $invoice->invoice_number
                );

                continue;
            }

            /**
             * =========================================
             * SEND REMINDER EMAIL
             * =========================================
             */
            Mail::to(
                $invoice->client->company_email
            )->send(
                new InvoiceReminderMail(
                    $invoice
                )
            );

            /**
             * =========================================
             * SUCCESS LOG
             * =========================================
             */
            $this->info(
                'Reminder sent: '
                . $invoice->invoice_number
            );
        }

        /**
         * =========================================
         * COMPLETE
         * =========================================
         */
        $this->info(
            'Invoice reminders completed.'
        );
    }
}