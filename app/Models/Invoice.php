<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'client_id',
        'service_id',
        'issue_date',
        'due_date',
        'subtotal',
        'ppn_enabled',
        'ppn_percentage',
        'ppn_amount',
        'pph23_enabled',
        'pph23_percentage',
        'pph23_amount',
        'total',
        'status',
        'payment_method',
        'notes',
        'terms',
        'paid_at',
        'created_by'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(
            InvoiceItem::class,
            'invoice_id'
        );
    }

    public function payments()
    {
        return $this->hasMany(
            Payment::class,
            'invoice_id'
        );
    }
}
