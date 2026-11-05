/**
 * Per-item tax (PPN / PPh 23) — mirrors app/Services/TaxCalculator.php
 *
 * PPN   = subtotal x PPN %   (added)
 * PPh23 = subtotal x PPh23 % (withheld, subtracted)
 */

export const DEFAULT_PPN = 11;
export const DEFAULT_PPH23 = 2;

export type ItemTax = {
    ppn_enabled: boolean;
    ppn_percentage: number;
    pph23_enabled: boolean;
    pph23_percentage: number;
};

export const defaultItemTax = (): ItemTax => ({
    ppn_enabled: false,
    ppn_percentage: DEFAULT_PPN,
    pph23_enabled: false,
    pph23_percentage: DEFAULT_PPH23,
});

/** Tax settings of an item loaded from the server (edit form). */
export const itemTaxFrom = (item: any): ItemTax => ({
    ppn_enabled: Boolean(item?.ppn_enabled),
    ppn_percentage: Number(item?.ppn_enabled ? item.ppn_percentage : DEFAULT_PPN),
    pph23_enabled: Boolean(item?.pph23_enabled),
    pph23_percentage: Number(item?.pph23_enabled ? item.pph23_percentage : DEFAULT_PPH23),
});

const round2 = (value: number) => Math.round(value * 100) / 100;

export const ppnOf = (subtotal: number, tax: ItemTax) =>
    tax.ppn_enabled ? round2(subtotal * (Number(tax.ppn_percentage) / 100)) : 0;

export const pph23Of = (subtotal: number, tax: ItemTax) =>
    tax.pph23_enabled ? round2(subtotal * (Number(tax.pph23_percentage) / 100)) : 0;

export const taxSummary = (rows: { subtotal: number; tax: ItemTax }[]) => {
    let subtotal = 0;
    let ppn = 0;
    let pph23 = 0;

    for (const row of rows) {
        subtotal += row.subtotal;
        ppn += ppnOf(row.subtotal, row.tax);
        pph23 += pph23Of(row.subtotal, row.tax);
    }

    return {
        subtotal,
        ppn,
        pph23,
        total: subtotal + ppn - pph23,
    };
};

/** "11% · Rp 110.000" or "-" for item tables. */
export const formatItemTax = (enabled: any, percentage: any, amount: any) =>
    enabled
        ? `${Number(percentage)}% · Rp ${Number(amount).toLocaleString('id-ID')}`
        : '-';
