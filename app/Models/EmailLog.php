<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One e-mail sent by the application (see App\Services\MailLogger).
 */
class EmailLog extends Model
{
    public const TYPES = [
        'invoice' => 'Invoice',
        'invoice_reminder' => 'Reminder Invoice',
        'payment_received' => 'Pembayaran Diterima',
        'visit_request' => 'Pengajuan Kunjungan',
        'other' => 'Lainnya',
    ];

    /** a log stuck in "sending" longer than this is shown as unconfirmed */
    public const UNCONFIRMED_AFTER_MINUTES = 10;

    protected $fillable = [
        'mailable',
        'type',
        'context',
        'related_type',
        'related_id',
        'client_id',
        'subject',
        'from',
        'to',
        'cc',
        'bcc',
        'html_body',
        'attachments',
        'status',
        'error_message',
        'message_id',
        'sent_at',
        'failed_at',
        'triggered_by',
        'source',
        'resent_from_id',
    ];

    protected $casts = [
        'context' => 'array',
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
        'sent_at' => 'datetime:Y-m-d H:i:s',
        'failed_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    /** never send the (large) HTML body in list payloads */
    protected $hidden = ['html_body'];

    protected $appends = ['display_status', 'type_label'];

    /**
     * Failed e-mails nobody has fixed yet: no e-mail of the same type for the
     * same invoice / visit was sent successfully afterwards (by "Kirim Ulang"
     * or by the normal flow, e.g. "Send Invoice" again).
     */
    public function scopeUnresolvedFailures(Builder $query): Builder
    {
        return $query->where('status', 'failed')
            ->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('email_logs as later')
                    ->whereColumn('later.type', 'email_logs.type')
                    ->whereColumn('later.related_type', 'email_logs.related_type')
                    ->whereColumn('later.related_id', 'email_logs.related_id')
                    ->where('later.status', 'sent')
                    ->whereColumn('later.id', '>', 'email_logs.id');
            });
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function resentFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'resent_from_id');
    }

    public function resends(): HasMany
    {
        return $this->hasMany(self::class, 'resent_from_id')->latest('id');
    }

    /** sent | failed | sending | unconfirmed */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'sending'
            && $this->created_at?->lt(now()->subMinutes(self::UNCONFIRMED_AFTER_MINUTES))) {
            return 'unconfirmed';
        }

        return $this->status;
    }

    public function getTypeLabelAttribute(): string
    {
        // relations may be loaded with a column subset (no `type`)
        return self::TYPES[$this->type ?? ''] ?? (string) $this->type;
    }
}
