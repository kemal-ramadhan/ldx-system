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

    /**
     * =========================================
     * CONSTRUCTOR
     * =========================================
     */
    public function __construct(
        Invoice $invoice
    ) {

        $this->invoice = $invoice;
    }

    /**
     * =========================================
     * ENVELOPE
     * =========================================
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject:
                'Invoice Payment Reminder - '
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
        return [];
    }
}