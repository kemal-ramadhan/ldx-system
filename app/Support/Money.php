<?php

namespace App\Support;

/**
 * Rupiah formatting for PDF / email that keeps cents.
 * Mirrors resources/js/lib/money.ts:
 *   1500000    -> "1.500.000"
 *   1500000.5  -> "1.500.000,50"
 */
class Money
{
    public static function format($value): string
    {
        $amount = round((float) $value, 2);
        $hasCents = fmod(abs($amount) * 100, 100) != 0;

        return number_format($amount, $hasCents ? 2 : 0, ',', '.');
    }
}
