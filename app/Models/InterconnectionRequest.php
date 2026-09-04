<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InterconnectionRequest extends Model
{
    protected $fillable = [
        'request_number',
        'requester_client_id',
        'source_port_id',
        'destination_port_id',
        'interconnection_type',
        'cable_type',
        'connector_type',
        'cable_length',
        'cable_length_unit',
        'status',
        'progress',
        'destination_approved_by',
        'destination_approved_at',
        'dc_approved_by',
        'dc_approved_at',
        'requested_at',
        'started_at',
        'completed_at',
        'description',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'cable_length' => 'decimal:2',
        'destination_approved_at' => 'datetime',
        'dc_approved_at' => 'datetime',
        'requested_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function requesterClient(): BelongsTo
    {
        return $this->belongsTo(
            Client::class,
            'requester_client_id'
        );
    }

    public function sourcePort(): BelongsTo
    {
        return $this->belongsTo(
            DevicePort::class,
            'source_port_id'
        );
    }

    public function destinationPort(): BelongsTo
    {
        return $this->belongsTo(
            DevicePort::class,
            'destination_port_id'
        );
    }

    public function destinationApprover(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'destination_approved_by'
        );
    }

    public function dcApprover(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dc_approved_by'
        );
    }

    public function crossConnect(): HasOne
    {
        return $this->hasOne(
            CrossConnect::class
        );
    }
}