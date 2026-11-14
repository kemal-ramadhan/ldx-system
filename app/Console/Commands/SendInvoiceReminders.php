<?php

namespace App\Console\Commands;

use App\Mail\InvoiceReminderMail;
use App\Services\MailLogger;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
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
        'Send invoice reminders (H-7, due date) and mark overdue invoices';

    /**
     * =========================================
     * HANDLE
     * =========================================
     * Each invoice gets at most one email per stage:
     * - upcoming  : 7 days before due date (skipped when the
     *               invoice was issued today, the invoice email
     *               already informed the client)
     * - due_today : on the due date
     * - overdue   : the day after the due date, status -> overdue
     */
    public function handle(): void
    {
        $this->info('Starting invoice reminders...');

        $today = now()->toDateString();

        $upcoming = Invoice::with(['client', 'service', 'items'])
            ->whereIn('status', ['pending', 'sent'])
            ->whereDate('due_date', now()->addDays(7)->toDateString())
            ->whereDate('issue_date', '<', $today)
            ->get();

        $dueToday = Invoice::with(['client', 'service', 'items'])
            ->whereIn('status', ['pending', 'sent'])
            ->whereDate('due_date', $today)
            ->get();

        $overdue = Invoice::with(['client', 'service', 'items'])
            ->whereIn('status', ['pending', 'sent'])
            ->whereDate('due_date', '<', $today)
            ->get();

        foreach ($overdue as $invoice) {
            $invoice->update(['status' => 'overdue']);
        }

        $sent = 0;

        foreach (['upcoming' => $upcoming, 'due_today' => $dueToday, 'overdue' => $overdue] as $type => $invoices) {
            foreach ($invoices as $invoice) {
                if ($this->sendReminder($invoice, $type)) {
                    $sent++;
                }
            }
        }

        $this->info("Invoice reminders completed. {$sent} email(s) sent, {$overdue->count()} invoice(s) marked overdue.");
    }

    private function sendReminder(Invoice $invoice, string $type): bool
    {
        if (!$invoice->client?->company_email) {
            $this->warn("Client email not found for invoice: {$invoice->invoice_number}");
            return false;
        }

        try {
            self::deliverReminder($invoice, $type);

            $this->info("Reminder ({$type}) sent: {$invoice->invoice_number}");

            return true;
        } catch (\Throwable $th) {
            $this->error("Failed to send reminder ({$type}) for {$invoice->invoice_number}: {$th->getMessage()}");

            return false;
        }
    }

    /**
     * Build the PDF and send one reminder (throws on failure).
     * Also used by "Kirim Ulang" in Riwayat Email.
     */
    public static function deliverReminder(Invoice $invoice, string $type): void
    {
        $invoice->loadMissing(['client', 'service', 'items']);

        if (!$invoice->client?->company_email) {
            throw new \RuntimeException("Client email not found for invoice {$invoice->invoice_number}.");
        }

        $logoSrc = 'data:image/png;base64,' . base64_encode(
            file_get_contents(public_path('assets/logos/ldx-logo.png'))
        );
        $logoLdxSrc = 'data:image/png;base64,' . base64_encode(
            file_get_contents(public_path('assets/logos/ldx.png'))
        );

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'logoSrc' => $logoSrc,
            'logoLdxSrc' => $logoLdxSrc,
        ]);

        $directory = storage_path('app/public/invoices');

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $filePath = $directory . '/' . $invoice->invoice_number . '.pdf';

        file_put_contents($filePath, $pdf->output());

        // recorded in Riwayat Email (failed sends are marked there)
        MailLogger::guard(fn () => Mail::to($invoice->client->company_email)->send(
            new InvoiceReminderMail($invoice, $filePath, $type)
        ));
    }
}
