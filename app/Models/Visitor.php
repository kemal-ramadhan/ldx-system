<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $table = 'visitors';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'nik',
        'company_name',
        'position_in_company',
        'type_of_visit',
        'visit_purpose',
        'visit_note',
        'visit_date',
        'checkin_time',
        'checkout_time',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'checkin_time' => 'datetime',
        'checkout_time' => 'datetime',
    ];

    public function visitorAttemps()
    {
        return $this->hasMany(VisitorAttemp::class);
    }
}
