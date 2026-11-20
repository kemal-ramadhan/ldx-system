<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $table = 'services';

    protected $fillable = [
        'client_id',
        'rack_id',
        'code',
        'name',
        'description',
        'billing_cycle',
        'start_date',
        'end_date',
        'next_due_date',
        'ppn_enabled',
        'ppn_percentage',
        'ppn_amount',
        'pph23_enabled',
        'pph23_percentage',
        'pph23_amount',
        'monthly_total',
        'total',
        'status',
    ];

    /** invoices are generated automatically this many days before next_due_date */
    public const AUTO_INVOICE_DAYS_BEFORE = 7;

    protected $appends = ['schedule_overdue', 'auto_invoice_date'];

    /**
     * Active services whose next due date has already passed: the scheduler
     * no longer invoices these automatically — an admin has to check them
     * (generate manually if the period was never billed, or fix the date).
     */
    public function scopeScheduleOverdue(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<', today()->toDateString());
    }

    /** true when the next due date has passed (see scopeScheduleOverdue) */
    public function getScheduleOverdueAttribute(): bool
    {
        $due = $this->attributes['next_due_date'] ?? null;

        return ($this->attributes['status'] ?? null) === 'active'
            && $due
            && Carbon::parse($due)->lt(today());
    }

    /** date the scheduler will create the next invoice (Y-m-d), null if it won't */
    public function getAutoInvoiceDateAttribute(): ?string
    {
        $due = $this->attributes['next_due_date'] ?? null;

        if (! $due || ($this->attributes['status'] ?? null) !== 'active' || $this->schedule_overdue) {
            return null;
        }

        $date = Carbon::parse($due)->subDays(self::AUTO_INVOICE_DAYS_BEFORE);

        return ($date->lt(today()) ? today() : $date)->toDateString();
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class, 'service_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'service_id');
    }
}
