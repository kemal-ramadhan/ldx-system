<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Rows per page for admin data tables (?per_page=10|25|50|100, default 10).
 *
 * The choice is remembered in the session per page (route), so it survives
 * search/filter requests that don't send per_page, and coming back later.
 * Anything else falls back to the default, so a query can't ask for huge pages.
 */
class PerPage
{
    public const OPTIONS = [10, 25, 50, 100];
    public const DEFAULT = 10;

    public static function from(?Request $request = null): int
    {
        $request ??= request();
        $key = 'per_page.' . ($request->route()?->getName() ?? $request->path());

        if ($request->has('per_page')) {
            $value = (int) $request->query('per_page');
            $value = in_array($value, self::OPTIONS, true) ? $value : self::DEFAULT;

            if ($request->hasSession()) {
                $request->session()->put($key, $value);
            }

            return $value;
        }

        $remembered = $request->hasSession() ? (int) $request->session()->get($key) : 0;

        return in_array($remembered, self::OPTIONS, true) ? $remembered : self::DEFAULT;
    }
}
