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

            $invoice = Invoice::create([
                'invoice_number' => self::generateInvoiceNumber(),
                'client_id' => $service->client_id,
                'service_id' => $service->id,
                'issue_date' => now(),
                'due_date' => $service->next_due_date,
                'subtotal' => $service->monthly_total,
                'ppn_enabled' => $service->ppn_enabled,
                'ppn_percentage' => $service->ppn_percentage,
                'ppn_amount' => $service->ppn_amount,
                'pph23_enabled' => $service->pph23_enabled,
                'pph23_percentage' => $service->pph23_percentage,
                'pph23_amount' => $service->pph23_amount,
                'total' => $service->total,
                'status' => 'sent',
                'created_by' => Auth::id(),
            ]);

            foreach ($service->serviceItems as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->subtotal,
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
