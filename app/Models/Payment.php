<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'invoice_id',
        'payment_number',
        'payment_method',
        'amount',
        'payment_date',
        'proof_of_payment',
        'payment_reference',
        'status',
        'verified_by',
        'verified_at',
        'notes',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }
}
