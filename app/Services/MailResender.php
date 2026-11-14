<?php

namespace App\Services;

use App\Console\Commands\SendInvoiceReminders;
use App\Http\Controllers\InvoiceController;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\VisitRequest;
use Illuminate\Validation\ValidationException;

/**
 * "Kirim Ulang" from Riwayat Email: rebuilds the e-mail with the CURRENT data
 * through the same code path as the normal send (fresh PDF, current recipients).
 * The new log is linked to the original one (resent_from_id).
 */
class MailResender
{
    public static function resend(EmailLog $log): EmailLog
    {
        $related = self::related($log);

        MailLogger::$resentFrom = $log->id;

        try {
            match ($log->type) {
                'invoice' => InvoiceController::deliverInvoiceEmail($related),
                'invoice_reminder' => SendInvoiceReminders::deliverReminder(
                    $related,
                    $log->context['reminder_type'] ?? 'upcoming'
                ),
                'payment_received' => InvoiceController::sendPaymentReceivedEmail($related),
                'visit_request' => self::resendVisit($related),
                default => throw ValidationException::withMessages([
                    'resend' => 'Jenis email ini tidak bisa dikirim ulang.',
                ]),
            };
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // the new log is already marked failed by MailLogger::guard()
            throw ValidationException::withMessages([
                'resend' => 'Gagal mengirim ulang: ' . mb_substr($e->getMessage(), 0, 300),
            ]);
        } finally {
            MailLogger::$resentFrom = null;
        }

        return EmailLog::where('resent_from_id', $log->id)->latest('id')->firstOrFail();
    }

    private static function related(EmailLog $log): Invoice|VisitRequest
    {
        $model = match ($log->related_type) {
            Invoice::class => Invoice::find($log->related_id),
            VisitRequest::class => VisitRequest::find($log->related_id),
            default => null,
        };

        if (! $model) {
            throw ValidationException::withMessages([
                'resend' => 'Data terkait (invoice / pengajuan) sudah tidak ada, email tidak bisa dikirim ulang.',
            ]);
        }

        return $model;
    }

    /** sendStatusMail() swallows errors itself; surface them for the admin */
    private static function resendVisit(VisitRequest $visit): void
    {
        if (! in_array($visit->status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'resend' => 'Pengajuan kunjungan sudah tidak berstatus disetujui/ditolak.',
            ]);
        }

        VisitRequestService::sendStatusMail($visit);

        $latest = EmailLog::where('resent_from_id', MailLogger::$resentFrom)->latest('id')->first();

        if (! $latest || $latest->status === 'failed') {
            throw new \RuntimeException($latest?->error_message ?? 'Email tidak terkirim.');
        }
    }
}
