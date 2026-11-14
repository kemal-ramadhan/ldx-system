<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Key-value settings editable from the admin panel.
 *
 *   AppSetting::get('mail.archive_bcc', [])
 *   AppSetting::put('mail.archive_bcc', ['a@x.id', 'b@x.id'])
 */
class AppSetting extends Model
{
    public const ARCHIVE_BCC = 'mail.archive_bcc';

    protected $fillable = ['key', 'value', 'updated_by'];

    protected $casts = [
        'value' => 'array',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    private static function cacheKey(string $key): string
    {
        return 'app_setting:' . $key;
    }

    /** null when the setting was never saved */
    public static function get(string $key, mixed $default = null): mixed
    {
        $record = Cache::rememberForever(self::cacheKey($key), fn () => self::where('key', $key)->first()?->only('value'));

        return $record === null ? $default : $record['value'];
    }

    public static function has(string $key): bool
    {
        return Cache::rememberForever(self::cacheKey($key), fn () => self::where('key', $key)->first()?->only('value')) !== null;
    }

    public static function put(string $key, mixed $value): self
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => Auth::id()]);

        Cache::forget(self::cacheKey($key));

        return $setting;
    }

    /**
     * Archive BCC list: admin setting first; .env MAIL_ARCHIVE_BCC only as a
     * fallback until the setting is saved once.
     */
    public static function archiveBcc(): array
    {
        $list = self::has(self::ARCHIVE_BCC)
            ? (array) self::get(self::ARCHIVE_BCC, [])
            : explode(',', (string) config('mail.archive_bcc'));

        return collect($list)
            ->map(fn ($address) => strtolower(trim((string) $address)))
            ->filter(fn ($address) => filter_var($address, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }
}
