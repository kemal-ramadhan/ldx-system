<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Rack;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $services = Service::with([
            'rack.room.locationDataCenter',
            'client'
        ])

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {

                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('billing_cycle', 'like', "%{$search}%")

                        ->orWhereHas('client', function ($client) use ($search) {
                            $client->where('name', 'like', "%{$search}%");
                        })

                        ->orWhereHas('rack', function ($rack) use ($search) {
                            $rack->where('name', 'like', "%{$search}%");
                        });
                });
            })

            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })

            ->latest()

            ->paginate(10)

            ->withQueryString();

        return Inertia::render('services/services/Services', [

            'title' => 'Services Management',

            'services' => $services,

            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('services/services/ServiceCreate', [

            'title' => 'Create Service',
            'clients' => Client::orderBy('company_name', 'asc')->get(['id', 'company_name']),
            'racks' => Rack::orderBy('name', 'asc')->get(['id', 'name']),
            'products' => Product::query()->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get([
                    'id',
                    'name',
                    'base_price',
                    'unit',
                    'billing_type'
                ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /**
             * =========================
             * SERVICE
             * =========================
             */
            'client_id' => 'required|exists:clients,id',
            'rack_id' => 'required|exists:racks,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'next_due_date' => 'required|date',
            'status' => 'required|in:pending,active,suspended,terminated',

            'ppn_enabled' => 'nullable|boolean',
            'ppn_percentage' => 'nullable|numeric|min:0|max:100',
            'pph23_enabled' => 'nullable|boolean',
            'pph23_percentage' => 'nullable|numeric|min:0|max:100',

            /**
             * =========================
             * ITEMS
             * =========================
             */
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['qty'] * $item['unit_price'];
            });

            $ppnEnabled = $request->boolean('ppn_enabled');

            $ppnPercentage = $ppnEnabled
                ? (float) ($validated['ppn_percentage'] ?? 0)
                : 0;

            $ppnAmount = $ppnEnabled
                ? $subtotal * ($ppnPercentage / 100)
                : 0;

            $pph23Enabled = $request->boolean('pph23_enabled');

            $pph23Percentage = $pph23Enabled
                ? (float) ($validated['pph23_percentage'] ?? 0)
                : 0;

            $pph23Amount = $pph23Enabled
                ? $subtotal * ($pph23Percentage / 100)
                : 0;
            
            $total = $subtotal + $ppnAmount - $pph23Amount;

            /**
             * =========================
             * CREATE SERVICE
             * =========================
             */
            $service = Service::create([
                'client_id' => $validated['client_id'],
                'rack_id' => $validated['rack_id'],
                'code' => $this->generateServiceCode(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'billing_cycle' => $validated['billing_cycle'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'next_due_date' => $validated['next_due_date'],
                'monthly_total' => $subtotal,
                'ppn_enabled' => $ppnEnabled, 
                'ppn_percentage' => $ppnPercentage,
                'ppn_amount' => $ppnAmount,
                'pph23_enabled' => $pph23Enabled, 
                'pph23_percentage' => $pph23Percentage,
                'pph23_amount' => $pph23Amount,
                'total' => $total,
                'status' => $validated['status'],
            ]);

            /**
             * =========================
             * CREATE SERVICE ITEMS
             * =========================
             */
            foreach ($validated['items'] as $item) {
                $subtotal =
                    $item['qty'] *
                    $item['unit_price'];

                ServiceItem::create([
                    'service_id' => $service->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'description' => null
                ]);
            }

            DB::commit();

            return redirect()
                ->route('services.index')
                ->with('success', 'Service created successfully.');
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $th->getMessage()
            ]);
        }
    }

    private function generateServiceCode()
    {
        do {

            $code = 'LDX.SR.' . strtoupper(
                \Illuminate\Support\Str::random(6)
            );
        } while (
            Service::query()->where('code', $code)->exists()
        );

        return $code;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $service = Service::with([

            /**
             * =========================
             * RELATIONS
             * =========================
             */
            'client',

            'rack.room.locationDataCenter',

            'serviceItems.product',
        ])->findOrFail($id);

        return Inertia::render('services/services/ServiceShow', [

            'title' => 'Service Detail',

            'service' => $service,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $service = Service::with([
            'serviceItems.product',
        ])->findOrFail($id);

        return Inertia::render('services/services/ServiceEdit', [
            'title' => 'Edit Service',
            'service' => $service,
            'clients' => Client::select(['id', 'company_name'])
                ->orderBy('company_name', 'asc')
                ->get(),
            'racks' => Rack::select(['id', 'name'])
                ->orderBy('name', 'asc')
                ->get(),

            'products' => Product::query()->where('status', 'active')->select([
                'id',
                'name',
                'base_price',
                'unit',
                'billing_type'
            ])
                ->orderBy('name', 'asc')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate([
            /**
             * =========================
             * SERVICE
             * =========================
             */
            'client_id' => 'required|exists:clients,id',
            'rack_id' => 'required|exists:racks,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'next_due_date' => 'required|date',
            'status' => 'required|in:pending,active,suspended,terminated',

            'ppn_enabled' => 'nullable|boolean',
            'ppn_percentage' => 'nullable|numeric|min:0|max:100',
            'pph23_enabled' => 'nullable|boolean',
            'pph23_percentage' => 'nullable|numeric|min:0|max:100',

            /**
             * =========================
             * ITEMS
             * =========================
             */
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            $ppnEnabled = $request->boolean('ppn_enabled');

            $ppnPercentage = $ppnEnabled
                ? (float) ($validated['ppn_percentage'] ?? 0)
                : 0;

            $ppnAmount = $ppnEnabled
                ? $subtotal * ($ppnPercentage / 100)
                : 0;

            $pph23Enabled = $request->boolean('pph23_enabled');

            $pph23Percentage = $pph23Enabled
                ? (float) ($validated['pph23_percentage'] ?? 0)
                : 0;

            $pph23Amount = $pph23Enabled
                ? $subtotal * ($pph23Percentage / 100)
                : 0;
            
            $total = $subtotal + $ppnAmount - $pph23Amount;

            /**
             * =========================
             * UPDATE SERVICE
             * =========================
             */
            $service->update([
                'client_id' => $validated['client_id'],
                'rack_id' => $validated['rack_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'billing_cycle' => $validated['billing_cycle'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'next_due_date' => $validated['next_due_date'],
                'monthly_total' => $subtotal,
                'ppn_enabled' => $ppnEnabled,
                'ppn_percentage' => $ppnPercentage,
                'ppn_amount' => $ppnAmount,
                'pph23_enabled' => $pph23Enabled,
                'pph23_percentage' => $pph23Percentage,
                'pph23_amount' => $pph23Amount,
                'total' => $total,
                'status' => $validated['status'],
            ]);

            /**
             * =========================
             * DELETE OLD ITEMS
             * =========================
             */
            ServiceItem::query()->where('service_id', $service->id)->delete();

            /**
             * =========================
             * INSERT NEW ITEMS
             * =========================
             */
            foreach ($validated['items'] as $item) {
                $subtotal =
                    $item['quantity'] *
                    $item['price'];
                ServiceItem::create([
                    'service_id' => $service->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('services.show', $service->id)
                ->with('success', 'Service updated successfully.');
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * =========================================
     * ACTIVATE
     * =========================================
     */
    public function activate(Service $service)
    {
        $service->update([
            'status' => 'active',
        ]);

        return back()->with(
            'success',
            'Service activated successfully.'
        );
    }

    /**
     * =========================================
     * SUSPEND
     * =========================================
     */
    public function suspend(Service $service)
    {
        $service->update([
            'status' => 'suspended',
        ]);

        return back()->with(
            'success',
            'Service suspended successfully.'
        );
    }

    /**
     * =========================================
     * TERMINATE
     * =========================================
     */
    public function terminate(Service $service)
    {
        $service->update([
            'status' => 'terminated',
        ]);

        return back()->with(
            'success',
            'Service terminated successfully.'
        );
    }

    /**
     * =========================================
     * GENERATE INVOICE
     * =========================================
     */
    public function generateInvoice(Service $service)
    {
        DB::beginTransaction();

        try {
            /**
             * =========================================
             * LOAD RELATIONS
             * =========================================
             */
            $service->load([
                'client',
                'serviceItems.product',
            ]);
            /**
             * =========================================
             * VALIDATE SERVICE
             * =========================================
             */
            if (
                $service->status !== 'active'
            ) {

                return back()->withErrors([
                    'error' => 'Only active services can generate invoices.'
                ]);
            }

            /**
             * =========================================
             * CHECK ITEMS
             * =========================================
             */
            if (
                $service->serviceItems->isEmpty()
            ) {

                return back()->withErrors([
                    'error' => 'Service has no items.'
                ]);
            }

            /**
             * =========================================
             * GENERATE INVOICE NUMBER
             * =========================================
             */
            $invoiceNumber = $this->generateInvoiceNumber();

            /**
             * =========================================
             * CREATE INVOICE
             * =========================================
             */
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
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
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            /**
             * =========================================
             * CREATE INVOICE ITEMS
             * =========================================
             */
            foreach (
                $service->serviceItems
                as $item
            ) {

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

            /**
             * =========================================
             * UPDATE NEXT DUE DATE
             * =========================================
             */
            $nextDueDate = Carbon::parse(
                $service->next_due_date
            );

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

            $service->update([
                'next_due_date' => $nextDueDate,
            ]);

            DB::commit();

            /**
             * =========================================
             * REDIRECT
             * =========================================
             */
            return redirect(
                "/admin/invoices/{$invoice->id}"
            )->with(
                'success',
                'Invoice generated successfully.'
            );
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * =========================================
     * GENERATE INVOICE NUMBER
     * =========================================
     */
    private function generateInvoiceNumber()
    {
        $date = now()->format('Ym');

        $lastInvoice = Invoice::query()->where(
            'invoice_number',
            'like',
            "INV-{$date}-%"
        )
            ->latest('id')
            ->first();

        if ($lastInvoice) {

            $lastNumber = (int) substr(
                $lastInvoice->invoice_number,
                -4
            );

            $newNumber = $lastNumber + 1;
        } else {

            $newNumber = 1;
        }

        return sprintf(
            'INV-%s-%04d',
            $date,
            $newNumber
        );
    }
}
