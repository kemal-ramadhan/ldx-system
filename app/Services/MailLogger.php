<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\VisitRequest;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

/**
 * Records every e-mail the app sends (SMTP keeps no copy in "Sent").
 *
 *  - MessageSending : add archive BCC, create the log ('sending') with an HTML snapshot
 *  - MessageSent    : mark it 'sent'
 *  - guard()        : wrap a send call; an exception marks the log 'failed' and is re-thrown
 *
 * Logging must never break sending: every step swallows its own errors.
 */
class MailLogger
{
    public const HEADER = 'X-LDX-Mail-Log';

    /** log of the message currently being sent in this process */
    public static ?int $current = null;

    /** set by MailResender so the new log points to the original one */
    public static ?int $resentFrom = null;

    private const TYPES = [
        \App\Mail\InvoiceMail::class => 'invoice',
        \App\Mail\InvoiceReminderMail::class => 'invoice_reminder',
        \App\Mail\PaymentReceivedMail::class => 'payment_received',
        \App\Mail\VisitRequestStatusMail::class => 'visit_request',
    ];

    /*
    |--------------------------------------------------------------------------
    | EVENTS
    |--------------------------------------------------------------------------
    */
    public static function sending(MessageSending $event): void
    {
        self::$current = null;

        try {
            $message = $event->message;
            self::addArchiveBcc($message);

            $mailable = $event->data['__laravel_mailable'] ?? null;
            [$related, $clientId, $context] = self::relatedFrom($event->data);

            $log = EmailLog::create([
                'mailable' => $mailable,
                'type' => self::TYPES[$mailable] ?? 'other',
                'context' => $context ?: null,
                'related_type' => $related ? $related::class : null,
                'related_id' => $related?->getKey(),
                'client_id' => $clientId,
                'subject' => $message->getSubject(),
                'from' => self::addresses($message->getFrom())[0] ?? null,
                'to' => self::addresses($message->getTo()),
                'cc' => self::addresses($message->getCc()),
                'bcc' => self::addresses($message->getBcc()),
                'html_body' => self::htmlSnapshot($message),
                'attachments' => self::attachmentList($message),
                'status' => 'sending',
                'triggered_by' => Auth::id(),
                'source' => app()->runningInConsole() ? 'console' : 'web',
                'resent_from_id' => self::$resentFrom,
            ]);

            $message->getHeaders()->addTextHeader(self::HEADER, (string) $log->id);
            self::$current = $log->id;
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function sent(MessageSent $event): void
    {
        try {
            $id = self::logIdOf($event->message) ?? self::$current;

            if ($id) {
                EmailLog::whereKey($id)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'message_id' => $event->sent->getMessageId(),
                    'error_message' => null,
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            self::$current = null;
        }
    }

    /**
     * Run a send call; on failure mark the current log 'failed' and re-throw,
     * so the caller's existing error handling keeps working.
     */
    public static function guard(callable $send): mixed
    {
        self::$current = null;

        try {
            return $send();
        } catch (\Throwable $e) {
            self::markFailed($e);

            throw $e;
        }
    }

    public static function markFailed(\Throwable $e): void
    {
        try {
            if (self::$current) {
                EmailLog::whereKey(self::$current)->where('status', 'sending')->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => self::safeError($e),
                ]);
            }
        } catch (\Throwable $inner) {
            report($inner);
        } finally {
            self::$current = null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    private static function addArchiveBcc(Email $message): void
    {
        // set in Riwayat Email -> Pengaturan BCC Arsip (falls back to .env until saved)
        $archive = collect(\App\Models\AppSetting::archiveBcc());

        $already = collect($message->getTo())->merge($message->getCc())->merge($message->getBcc())
            ->map(fn (Address $a) => strtolower($a->getAddress()));

        foreach ($archive as $address) {
            if (! $already->contains(strtolower($address))) {
                $message->addBcc($address);
            }
        }
    }

    /** related model + client from the mailable's view data */
    private static function relatedFrom(array $data): array
    {
        $context = [];

        if (($data['invoice'] ?? null) instanceof Invoice) {
            $invoice = $data['invoice'];

            if (isset($data['type']) && is_string($data['type'])) {
                $context['reminder_type'] = $data['type'];
            }
            $context['invoice_number'] = $invoice->invoice_number;

            return [$invoice, $invoice->client_id, $context];
        }

        if (($data['visit'] ?? null) instanceof VisitRequest) {
            $visit = $data['visit'];
            $context['request_number'] = $visit->request_number;
            $context['status'] = $visit->status;

            return [$visit, $visit->client_id, $context];
        }

        return [null, null, $context];
    }

    private static function addresses(array $list): array
    {
        return array_values(array_map(
            fn (Address $a) => $a->getName() ? "{$a->getName()} <{$a->getAddress()}>" : $a->getAddress(),
            $list
        ));
    }

    /** HTML with inline images (cid:) turned into data URIs so the preview shows them */
    private static function htmlSnapshot(Email $message): ?string
    {
        $html = $message->getHtmlBody();

        if (is_resource($html)) {
            $html = stream_get_contents($html);
        }

        if (! $html) {
            $text = $message->getTextBody();

            return $text ? '<pre style="white-space:pre-wrap;font-family:sans-serif">' . e(is_resource($text) ? stream_get_contents($text) : $text) . '</pre>' : null;
        }

        foreach ($message->getAttachments() as $part) {
            if ($part instanceof DataPart && $part->getDisposition() === 'inline' && $part->hasContentId()) {
                $dataUri = 'data:' . $part->getContentType() . ';base64,' . base64_encode($part->getBody());
                $html = str_replace('cid:' . $part->getContentId(), $dataUri, $html);
            }
        }

        return $html;
    }

    private static function attachmentList(Email $message): array
    {
        $list = [];

        foreach ($message->getAttachments() as $part) {
            if ($part instanceof DataPart && $part->getDisposition() !== 'inline') {
                $list[] = [
                    'name' => $part->getFilename(),
                    'type' => $part->getContentType(),
                    'size' => strlen($part->getBody()),
                ];
            }
        }

        return $list;
    }

    private static function logIdOf(Email $message): ?int
    {
        $value = $message->getHeaders()->get(self::HEADER)?->getBodyAsString();

        return $value ? (int) $value : null;
    }

    /** error text without credentials */
    private static function safeError(\Throwable $e): string
    {
        $text = $e->getMessage();

        foreach (array_filter([config('mail.mailers.smtp.password'), config('mail.mailers.smtp.username')]) as $secret) {
            $text = str_replace($secret, '***', $text);
        }

        return mb_substr($text, 0, 2000);
    }
}
