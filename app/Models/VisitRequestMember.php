<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A person joining a visit request.
 */
class VisitRequestMember extends Model
{
    protected $fillable = [
        'visit_request_id',
        'name',
        'nik',
        'phone',
        'email',
        'address',
        'company_name',
        'position_in_company',
        'visitor_id',
        'checked_in_at',
        'checked_in_by',
        'checked_out_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime:Y-m-d H:i',
        'checked_out_at' => 'datetime:Y-m-d H:i',
    ];

    public function visitRequest(): BelongsTo
    {
        return $this->belongsTo(VisitRequest::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * "3171************" for the public check-in page.
     * The LAST 4 digits are the check-in verification, so they are never shown.
     */
    public function getMaskedNikAttribute(): string
    {
        $nik = (string) $this->nik;
        $visible = strlen($nik) > 8 ? 4 : 0;

        return substr($nik, 0, $visible) . str_repeat('*', strlen($nik) - $visible);
    }

    public function nikMatchesLastFour(string $digits): bool
    {
        $nik = preg_replace('/\s+/', '', (string) $this->nik);

        return strlen($digits) === 4 && hash_equals(substr($nik, -4), $digits);
    }
}
