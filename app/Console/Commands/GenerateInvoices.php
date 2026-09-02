<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Service;
use App\Mail\InvoiceMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
        'Generate recurring invoices automatically';

    /**
     * =========================================
     * HANDLE
     * =========================================
     */
    public function handle(): void
    {

        $this->info(
            'Starting recurring invoice generation...'
        );

        /**
         * =========================================
         * GET ACTIVE SERVICES
         * =========================================
         */
        $services = Service::with('client')
            ->where(
                'status',
                'active'
            )
            ->whereDate(
                'next_due_date',
                '<=',
                now()->toDateString()
            )
            ->get();

        /**
         * =========================================
         * NO SERVICES
         * =========================================
         */
        if ($services->count() === 0) {

            $this->warn(
                'No services due today.'
            );

            return;
        }

        /**
         * =========================================
         * LOOP SERVICES
         * =========================================
         */
        foreach ($services as $service) {

            /**
             * =========================================
             * CHECK EXISTING INVOICE
             * =========================================
             */
            $existingInvoice = Invoice::where(
                'service_id',
                $service->id
            )
            ->whereMonth(
                'issue_date',
                now()->month
            )
            ->whereYear(
                'issue_date',
                now()->year
            )
            ->exists();

            /**
             * =========================================
             * SKIP IF EXISTS
             * =========================================
             */
            if ($existingInvoice) {

                $this->warn(
                    'Invoice already exists for service: '
                    . $service->name
                );

                continue;
            }

            /**
             * =========================================
             * GENERATE INVOICE NUMBER
             * =========================================
             */
            $invoiceNumber =
                'INV-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(
                    uniqid()
                );

            /**
             * =========================================
             * CREATE INVOICE
             * =========================================
             */
            $invoice = Invoice::create([
                'invoice_number' =>
                    $invoiceNumber,
                'client_id' =>
                    $service->client_id,
                'service_id' =>
                    $service->id,
                'issue_date' =>
                    now(),
                'due_date' =>
                    now()->addDays(7),
                'subtotal' =>
                    $service->monthly_total,
                'tax' => 0,
                'total' =>
                    $service->monthly_total,
                'status' =>
                    'sent',
            ]);

            /**
             * =========================================
             * UPDATE NEXT DUE DATE
             * =========================================
             */
            $nextDate = Carbon::parse(
                $service->next_due_date
            );

            switch ($service->billing_cycle) {

                /**
                 * =========================================
                 * MONTHLY
                 * =========================================
                 */
                case 'monthly':

                    $nextDate->addMonth();

                    break;

                /**
                 * =========================================
                 * QUARTERLY
                 * =========================================
                 */
                case 'quarterly':

                    $nextDate->addMonths(3);

                    break;

                /**
                 * =========================================
                 * YEARLY
                 * =========================================
                 */
                case 'yearly':

                    $nextDate->addYear();

                    break;

                /**
                 * =========================================
                 * ONE TIME
                 * =========================================
                 */
                case 'one_time':

                    $service->update([
                        'status' => 'inactive',
                    ]);

                    break;
            }

            /**
             * =========================================
             * UPDATE SERVICE
             * =========================================
             */
            if (
                $service->billing_cycle !== 'one_time'
            ) {

                $service->update([
                    'next_due_date' => $nextDate,
                ]);
            }

            /**
             * =========================================
             * SEND EMAIL
             * =========================================
             */
            /**
             * =========================================
             * GENERATE PDF
             * =========================================
             */
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'pdf.invoice',
                [
                    'invoice' => $invoice
                ]
            );

            /**
             * =========================================
             * FILE NAME
             * =========================================
             */
            $fileName =
                $invoice->invoice_number . '.pdf';

            /**
             * =========================================
             * STORAGE PATH
             * =========================================
             */
            $filePath =
                storage_path(
                    'app/public/invoices/' . $fileName
                );

            /**
             * =========================================
             * CREATE DIRECTORY
             * =========================================
             */
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

            /**
             * =========================================
             * SAVE PDF
             * =========================================
             */
            file_put_contents(
                $filePath,
                $pdf->output()
            );

            /**
             * =========================================
             * SEND EMAIL
             * =========================================
             */
            if (
                $service->client?->company_email
            ) {

                Mail::to(
                    $service->client->company_email
                )->send(
                    new InvoiceMail(
                        $invoice,
                        $filePath
                    )
                );
            }

            /**
             * =========================================
             * SUCCESS MESSAGE
             * =========================================
             */
            $this->info(
                'Invoice created successfully: '
                . $invoice->invoice_number
            );
        }

        /**
         * =========================================
         * DONE
         * =========================================
         */
        $this->info(
            'Recurring invoice generation completed.'
        );
    }
}