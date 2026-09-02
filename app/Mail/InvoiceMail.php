<?php

namespace App\Mail;

use App\Models\Invoice;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    public $pdfPath;

    /**
     * =========================================
     * CONSTRUCTOR
     * =========================================
     */
    public function __construct(
        Invoice $invoice,
        string $pdfPath
    ) {

        $this->invoice = $invoice;

        $this->pdfPath = $pdfPath;
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
                'Invoice ' .
                $this->invoice->invoice_number .
                ' - LDX Data Center',
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
            view: 'emails.invoice',
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

            Attachment::fromPath(
                $this->pdfPath
            )->as(
                $this->invoice->invoice_number . '.pdf'
            ),
        ];
    }
}