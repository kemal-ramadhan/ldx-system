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
             * GENERATE PDF
             * =========================================
             */
            $invoice->load([
                'client',
                'service',
                'items',
            ]);

            $logoPath = public_path('assets/logos/ldx-logo.png');
            $logoLdx = public_path('assets/logos/ldx.png');

            $logoData = base64_encode(
                file_get_contents($logoPath)
            );

            $logoDataLdx = base64_encode(
                file_get_contents($logoLdx)
            );

            $logoSrc = 'data:image/png;base64,' . $logoData;
            $logoLdxSrc = 'data:image/png;base64,' . $logoDataLdx;

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'pdf.invoice',
                [
                    'invoice' => $invoice,
                    'logoSrc' => $logoSrc,
                    'logoLdxSrc' => $logoLdxSrc,
                ]
            );

            $fileName =
                $invoice->invoice_number . '.pdf';

            $filePath =
                storage_path(
                    'app/public/invoices/' . $fileName
                );

            if (
                !file_exists(
                    storage_path('app/public/invoices')
                )
            ) {

                mkdir(
                    storage_path('app/public/invoices'),
                    0777,
                    true
                );
            }

            file_put_contents(
                $filePath,
                $pdf->output()
            );

            /**
             * =========================================
             * SEND REMINDER EMAIL
             * =========================================
             */
            Mail::to(
                $invoice->client->company_email
            )->send(
                new InvoiceReminderMail(
                    $invoice,
                    $filePath
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