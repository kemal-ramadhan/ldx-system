<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Photo / BAST document of a device movement (same pattern as RackImage).
 */
class DeviceMovementAttachment extends Model
{
    protected $fillable = [
        'device_movement_id',
        'type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    protected $appends = ['url', 'is_image'];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(DeviceMovement::class, 'device_movement_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->file_type, 'image/');
    }
}
