<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrossConnect extends Model
{
    protected $fillable = [
        'cross_connect_number',
        'interconnection_request_id',
        'source_port_id',
        'destination_type',
        'destination_port_id',
        'external_client_name',
        'external_rack_name',
        'external_device_name',
        'external_port_name',
        'cable_type',
        'connector_type',
        'cable_length',
        'cable_length_unit',
        'status',
        'installed_by',
        'installed_at',
        'terminated_at',
        'description',
        'notes',
    ];

    protected $casts = [
        'cable_length' => 'decimal:2',
        'installed_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function interconnectionRequest(): BelongsTo
    {
        return $this->belongsTo(
            InterconnectionRequest::class,
            'interconnection_request_id'
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

    public function installer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'installed_by'
        );
    }
}