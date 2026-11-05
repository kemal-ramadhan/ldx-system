<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use SerializesModels;

    public $invoice;

    public $pdfPath;

    /**
     * upcoming (H-7) | due_today (H-0) | overdue
     */
    public $type;

    /**
     * =========================================
     * CONSTRUCTOR
     * =========================================
     */
    public function __construct(
        Invoice $invoice,
        string $pdfPath,
        string $type = 'upcoming'
    ) {

        $this->invoice = $invoice;
        $this->pdfPath = $pdfPath;
        $this->type = $type;
    }

    /**
     * =========================================
     * ENVELOPE
     * =========================================
     */
    public function envelope(): Envelope
    {
        $prefix = match ($this->type) {
            'overdue' => 'Invoice Overdue',
            'due_today' => 'Invoice Due Today',
            default => 'Invoice Payment Reminder',
        };

        return new Envelope(
            subject:
                $prefix . ' - '
                . $this->invoice->invoice_number,
        );
    }

    /**
     * =========================================
     * CONTENT
     * =========================================
     */
    public function content(): Content
    {
        return new Content(
            view:
                'emails.invoice-reminder',
        );
    }

    /**
     * =========================================
     * ATTACHMENTS
     * =========================================
     */
    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromPath(
                $this->pdfPath
            )->as(
                $this->invoice->invoice_number . '.pdf'
            ),
        ];
    }
}