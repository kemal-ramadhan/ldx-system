/**
 * Money formatting (Rupiah) that keeps cents.
 *
 * Intl's IDR currency format rounds to whole rupiah (1500.5 -> "Rp 1.501"),
 * which made invoices differ from the real nominal. These helpers show:
 *   1500000    -> "1.500.000"
 *   1500000.5  -> "1.500.000,50"
 */
export const formatNumber = (value: unknown): string => {
    const n = Number(value) || 0;
    const hasCents = Math.round(Math.abs(n) * 100) % 100 !== 0;

    return n.toLocaleString('id-ID', {
        minimumFractionDigits: hasCents ? 2 : 0,
        maximumFractionDigits: 2,
    });
};

export const formatRupiah = (value: unknown): string => `Rp ${formatNumber(value)}`;
