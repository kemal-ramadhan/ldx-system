<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'company_code',
        'company_name',
        'company_email',
        'company_phone',
        'company_npwp',
        'company_address',
        'company_city',
        'company_province',
        'company_postal_code',
        'contract_date',
        'contract_done_date',
        'status',
    ];

    /**
     * =========================
     * PICS
     * =========================
     */
    public function pics()
    {
        return $this->hasMany(ClientPic::class);
    }

    /**
     * =========================
     * RACK DEVICES
     * =========================
     */
    public function rackDevices()
    {
        return $this->hasMany(RackDivice::class, 'client_id');
    }

    /**
     * =========================
     * CLIENT RACKS
     * =========================
     */
    public function clientRacks()
    {
        return $this->hasMany(ClientRack::class, 'client_id');
    }
}
