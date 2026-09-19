<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevicePort extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rack_divice_id',
        'port_name',
        'port_number',
        'port_type',
        'connector_type',
        'status',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function device(): BelongsTo
    {
        return $this->belongsTo(
            RackDivice::class,
            'rack_divice_id'
        );
    }

    public function sourceInterconnectionRequests(): HasMany
    {
        return $this->hasMany(
            InterconnectionRequest::class,
            'source_port_id'
        );
    }

    public function destinationInterconnectionRequests(): HasMany
    {
        return $this->hasMany(
            InterconnectionRequest::class,
            'destination_port_id'
        );
    }

    public function sourceCrossConnects(): HasMany
    {
        return $this->hasMany(
            CrossConnect::class,
            'source_port_id'
        );
    }

    public function destinationCrossConnects(): HasMany
    {
        return $this->hasMany(
            CrossConnect::class,
            'destination_port_id'
        );
    }

    public function activeSourceCrossConnect()
    {
        return $this->hasOne(
            CrossConnect::class,
            'source_port_id'
        )->where('status', 'active');
    }

    public function activeDestinationCrossConnect()
    {
        return $this->hasOne(
            CrossConnect::class,
            'destination_port_id'
        )->where('status', 'active');
    }
}