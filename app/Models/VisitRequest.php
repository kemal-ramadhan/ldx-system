<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pengajuan kunjungan (client portal -> admin approval -> check-in).
 */
class VisitRequest extends Model
{
    public const TYPES = [
        'Kunjungan' => 'Kunjungan',
        'meeting' => 'Meeting',
        'inspeksi' => 'Inspeksi',
        'maintenance' => 'Pemeliharaan',
        'troubleshooting' => 'Troubleshooting',
        'deliveries' => 'Pengiriman',
        'lainnya' => 'Lainnya',
    ];

    public const STAGE_LABELS = [
        'pending' => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'in_progress' => 'Sedang Berkunjung',
        'completed' => 'Selesai',
        'expired' => 'Kedaluwarsa',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'request_number',
        'client_id',
        'requested_by',
        'visit_date',
        'start_time',
        'end_time',
        'type_of_visit',
        'purpose',
        'notes',
        'status',
        'checkin_code',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'admin_notes',
        'cancelled_at',
    ];

    protected $casts = [
        'visit_date' => 'date:Y-m-d',
        'reviewed_at' => 'datetime:Y-m-d H:i',
        'cancelled_at' => 'datetime:Y-m-d H:i',
        // serialized in app timezone (Asia/Jakarta), not UTC ISO
        'created_at' => 'datetime:Y-m-d H:i',
    ];

    protected $appends = ['stage', 'stage_label', 'type_label'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function racks(): BelongsToMany
    {
        return $this->belongsToMany(Rack::class, 'visit_request_racks');
    }

    public function members(): HasMany
    {
        return $this->hasMany(VisitRequestMember::class)->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */
    public function isVisitDay(): bool
    {
        return $this->visit_date?->isToday() ?? false;
    }

    public function canCheckInToday(): bool
    {
        return $this->status === 'approved' && $this->isVisitDay();
    }

    public function isEditable(): bool
    {
        return $this->status === 'pending';
    }

    /** pending/approved and the visit day has not passed */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true)
            && $this->visit_date?->endOfDay()->isFuture()
            && ! $this->members->whereNotNull('checked_in_at')->count();
    }

    /**
     * Display stage: status + check-in progress.
     */
    public function getStageAttribute(): string
    {
        if ($this->status !== 'approved') {
            return $this->status;
        }

        $members = $this->relationLoaded('members') ? $this->members : $this->members()->get();
        $checkedIn = $members->whereNotNull('checked_in_at')->count();

        if ($members->count() > 0 && $checkedIn === $members->count()) {
            return 'completed';
        }

        if ($checkedIn > 0) {
            return 'in_progress';
        }

        if ($this->visit_date && $this->visit_date->endOfDay()->isPast()) {
            return 'expired';
        }

        return 'approved';
    }

    public function getStageLabelAttribute(): string
    {
        return self::STAGE_LABELS[$this->stage] ?? $this->stage;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type_of_visit] ?? (string) $this->type_of_visit;
    }

    /**
     * VR-YYYYMM-00001 (call inside a transaction).
     */
    public static function nextNumber(): string
    {
        $prefix = 'VR-' . now()->format('Ym') . '-';

        $last = self::query()
            ->where('request_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('request_number')
            ->value('request_number');

        return $prefix . str_pad((string) (($last ? (int) substr($last, -5) : 0) + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * 8-char check-in code without ambiguous characters (0/O, 1/I/L).
     */
    public static function newCheckinCode(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (self::where('checkin_code', $code)->exists());

        return $code;
    }
}
