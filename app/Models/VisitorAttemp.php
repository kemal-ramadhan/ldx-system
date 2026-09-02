<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorAttemp extends Model
{
    protected $table = 'visitor_attemps';

    protected $fillable = [
        'visitor_id',
        'file',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
