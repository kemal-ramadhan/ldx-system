<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RackImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rack_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'caption',
        'uploaded_by',
    ];

    protected $appends = ['url'];

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
