<?php

namespace App\Http\Controllers;

use App\Http\Controllers\InvoiceController;
use App\Models\Client;
use App\Models\Product;
use App\Models\Rack;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Services\ServiceInvoiceGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $services = Service::withCount('invoices')->with([
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
            Service::withTrashed()->where('code', $code)->exists()
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
        ])
            ->withCount('invoices')
            ->withExists(['invoices as has_paid_invoice' => fn ($q) => $q->where('status', 'paid')])
            ->findOrFail($id);

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
        $service = Service::findOrFail($id);

        try {
            // soft delete: invoices keep their service relation for reports
            $service->delete();
        } catch (\Throwable $th) {
            return back()->withErrors([
                'error' => 'Failed to delete service: ' . $th->getMessage(),
            ]);
        }

        // deleted from the detail page -> back to the list
        if (str_contains(url()->previous(), "/admin/services/{$service->id}")) {
            return redirect('/admin/services')->with('success', 'Service deleted successfully.');
        }

        return back()->with('success', 'Service deleted successfully.');
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
        try {
            $invoice = ServiceInvoiceGenerator::generate($service);
        } catch (\Throwable $th) {
            return back()->withErrors([
                'error' => $th->getMessage()
            ]);
        }

        /**
         * =========================================
         * SEND EMAIL TO CLIENT
         * =========================================
         * Runs after the response so the admin is redirected
         * immediately. If the email fails the invoice goes back
         * to pending, so it can be resent from the invoice page.
         */
        dispatch(function () use ($invoice) {
            try {
                InvoiceController::deliverInvoiceEmail($invoice);
            } catch (\Throwable $th) {
                $invoice->update([
                    'status' => 'pending',
                ]);

                Log::error('Failed to send invoice email', [
                    'invoice_id' => $invoice->id,
                    'error' => $th->getMessage(),
                ]);
            }
        })->afterResponse();

        // ?generated=1 opens the success popup on the invoice page
        return redirect(
            "/admin/invoices/{$invoice->id}?generated=1"
        );
    }
}
