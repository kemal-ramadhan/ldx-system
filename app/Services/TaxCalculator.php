<?php

namespace App\Services;

/**
 * Per-item tax (PPN / PPh 23) calculation.
 *
 * PPN   = item subtotal x PPN %   (added to the invoice)
 * PPh23 = item subtotal x PPh23 % (withheld, subtracted)
 * Total = sum(subtotal) + sum(PPN) - sum(PPh23)
 */
class TaxCalculator
{
    public const DEFAULT_PPN = 11;
    public const DEFAULT_PPH23 = 2;

    /**
     * Tax fields for one item.
     */
    public static function item(
        float $subtotal,
        bool $ppnEnabled,
        $ppnPercentage,
        bool $pph23Enabled,
        $pph23Percentage
    ): array {
        $ppnPct = $ppnEnabled ? (float) ($ppnPercentage ?? self::DEFAULT_PPN) : 0;
        $pphPct = $pph23Enabled ? (float) ($pph23Percentage ?? self::DEFAULT_PPH23) : 0;

        return [
            'ppn_enabled' => $ppnEnabled,
            'ppn_percentage' => $ppnPct,
            'ppn_amount' => round($subtotal * $ppnPct / 100, 2),
            'pph23_enabled' => $pph23Enabled,
            'pph23_percentage' => $pphPct,
            'pph23_amount' => round($subtotal * $pphPct / 100, 2),
        ];
    }

    /**
     * Header totals from items. Each item needs a subtotal under
     * $subtotalKey plus the fields returned by item().
     *
     * ppn_percentage / pph23_percentage hold the rate when every taxed
     * item uses the same rate, and 0 when rates are mixed.
     */
    public static function summary(iterable $items, string $subtotalKey = 'subtotal'): array
    {
        $subtotal = 0;
        $ppnAmount = 0;
        $pphAmount = 0;
        $ppnRates = [];
        $pphRates = [];

        foreach ($items as $item) {
            $item = is_array($item) ? $item : $item->toArray();

            $subtotal += (float) $item[$subtotalKey];

            if (!empty($item['ppn_enabled'])) {
                $ppnAmount += (float) $item['ppn_amount'];
                $ppnRates[(string) (float) $item['ppn_percentage']] = true;
            }

            if (!empty($item['pph23_enabled'])) {
                $pphAmount += (float) $item['pph23_amount'];
                $pphRates[(string) (float) $item['pph23_percentage']] = true;
            }
        }

        return [
            'subtotal' => round($subtotal, 2),
            'ppn_enabled' => count($ppnRates) > 0,
            'ppn_percentage' => count($ppnRates) === 1 ? (float) array_key_first($ppnRates) : 0,
            'ppn_amount' => round($ppnAmount, 2),
            'pph23_enabled' => count($pphRates) > 0,
            'pph23_percentage' => count($pphRates) === 1 ? (float) array_key_first($pphRates) : 0,
            'pph23_amount' => round($pphAmount, 2),
            'total' => round($subtotal + $ppnAmount - $pphAmount, 2),
        ];
    }
}
