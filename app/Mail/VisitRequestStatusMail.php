<?php

namespace App\Mail;

use App\Models\VisitRequest;
use App\Support\VisitQr;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Visit request approved (with check-in code + QR) or rejected.
 */
class VisitRequestStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public VisitRequest $visit)
    {
    }

    public function envelope(): Envelope
    {
        $label = $this->visit->status === 'approved' ? 'Disetujui' : 'Ditolak';

        return new Envelope(
            subject: "Pengajuan Kunjungan {$this->visit->request_number} {$label} - LDX Data Center",
        );
    }

    public function content(): Content
    {
        $approved = $this->visit->status === 'approved' && $this->visit->checkin_code;
        $checkinUrl = $approved ? VisitQr::checkinUrl($this->visit) : null;

        return new Content(
            view: 'emails.visit-request-status',
            with: [
                'visit' => $this->visit,
                'approved' => $approved,
                'checkinUrl' => $checkinUrl,
                'qrPng' => $approved ? VisitQr::png($checkinUrl) : null,
                'portalUrl' => url("/client/visit-requests/{$this->visit->id}"),
            ],
        );
    }
}
