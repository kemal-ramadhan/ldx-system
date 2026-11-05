<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Creates the invoice for a service's current billing period.
 * Used by the "Generate Invoice" button and the invoices:generate
 * schedule so both produce identical invoices.
 */
class ServiceInvoiceGenerator
{
    /**
     * =========================================
     * CREATE INVOICE + ITEMS, ADVANCE NEXT DUE DATE
     * =========================================
     */
    public static function generate(Service $service): Invoice
    {
        $service->loadMissing([
            'client',
            'serviceItems.product',
        ]);

        if (!in_array($service->status, ['pending', 'active'])) {
            throw new RuntimeException('Only pending or active services can generate invoices.');
        }

        if ($service->serviceItems->isEmpty()) {
            throw new RuntimeException('Service has no items.');
        }

        return DB::transaction(function () use ($service) {

            $items = $service->serviceItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => (float) $item->subtotal,
                ] + TaxCalculator::item(
                    (float) $item->subtotal,
                    (bool) $item->ppn_enabled,
                    $item->ppn_percentage,
                    (bool) $item->pph23_enabled,
                    $item->pph23_percentage,
                );
            });

            // header = sum of the items, so it always matches the item rows
            $summary = TaxCalculator::summary($items, 'total');

            $invoice = Invoice::create([
                'invoice_number' => self::generateInvoiceNumber(),
                'client_id' => $service->client_id,
                'service_id' => $service->id,
                'issue_date' => now(),
                'due_date' => $service->next_due_date,
                'subtotal' => $summary['subtotal'],
                'ppn_enabled' => $summary['ppn_enabled'],
                'ppn_percentage' => $summary['ppn_percentage'],
                'ppn_amount' => $summary['ppn_amount'],
                'pph23_enabled' => $summary['pph23_enabled'],
                'pph23_percentage' => $summary['pph23_percentage'],
                'pph23_amount' => $summary['pph23_amount'],
                'total' => $summary['total'],
                'status' => 'sent',
                'created_by' => Auth::id(),
            ]);

            foreach ($items as $item) {
                InvoiceItem::create($item + [
                    'invoice_id' => $invoice->id,
                ]);
            }

            $service->update([
                'next_due_date' => self::nextDueDate($service),
            ]);

            return $invoice;
        });
    }

    /**
     * =========================================
     * INVOICE ALREADY EXISTS FOR CURRENT PERIOD
     * =========================================
     */
    public static function periodInvoiceExists(Service $service): bool
    {
        return Invoice::where('service_id', $service->id)
            ->whereDate('due_date', Carbon::parse($service->next_due_date)->toDateString())
            ->exists();
    }

    private static function nextDueDate(Service $service): Carbon
    {
        $nextDueDate = Carbon::parse($service->next_due_date);

        switch ($service->billing_cycle) {
            case 'monthly':
                $nextDueDate->addMonth();
                break;
            case 'quarterly':
                $nextDueDate->addMonths(3);
                break;
            case 'yearly':
                $nextDueDate->addYear();
                break;
        }

        return $nextDueDate;
    }

    private static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ym');

        $lastInvoice = Invoice::query()
            ->where('invoice_number', 'like', "INV-{$date}-%")
            ->latest('id')
            ->first();

        $newNumber = $lastInvoice
            ? ((int) substr($lastInvoice->invoice_number, -4)) + 1
            : 1;

        return sprintf('INV-%s-%04d', $date, $newNumber);
    }
}
