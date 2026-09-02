<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $clientId = Auth::user()->clientPic?->client_id;

        $invoices = Invoice::with([
            'client',
            'service',
        ])
            ->where('client_id', $clientId)

            // tampilkan semua status kecuali draft & pending
            ->whereNotIn('status', [
                'draft',
                'pending'
            ])

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'invoice_number',
                        'like',
                        "%{$search}%"
                    );
                });
            })

            ->when($status, function ($query) use ($status) {
                $query->where(
                    'status',
                    $status
                );
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'billings/invoices/InvoiceClient',
            [
                'title' => 'My Invoices',
                'invoices' => $invoices,
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                ],
            ]
        );
    }

    public function paymentInvoice(string $id)
    {
        /**
         * =========================================
         * LOAD INVOICE
         * =========================================
         */
        $invoice = Invoice::with([
            'client',
            'service',
            'items',
            'payments',
        ])->findOrFail($id);

        /**
         * =========================================
         * RETURN PAGE
         * =========================================
         */
        return Inertia::render(
            'billings/payments/PaymentClient',
            [
                'title' => 'Payment Confirmation',
                'invoice' => $invoice,
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function storePayment(
        Request $request,
        Invoice $invoice
    ) {

        /**
         * =========================================
         * VALIDATION
         * =========================================
         */
        $request->validate([
            'payment_method' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'proof' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {

            /**
             * =========================================
             * PAYMENT PROOF
             * =========================================
             */
            $proofPath = null;

            /**
             * =========================================
             * UPLOAD FILE
             * =========================================
             */
            if ($request->hasFile('proof')) {

                $proofPath = $request
                    ->file('proof')
                    ->store(
                        'payments',
                        'public'
                    );
            }

            /**
             * =========================================
             * CREATE PAYMENT
             * =========================================
             */
            Payment::create([

                'invoice_id' =>
                $invoice->id,

                'payment_method' =>
                $request->payment_method,

                'amount' => $request->amount,
                'payment_date' => now(),
                'payment_number' => $request->reference_number,

                'notes' => $request->notes,

                'proof_of_payment' => $proofPath,

                /**
                 * =========================================
                 * DIRECT PAYMENT
                 * =========================================
                 */
                'status' => 'pending',
            ]);

            /**
             * =========================================
             * UPDATE INVOICE
             * =========================================
             */
            $invoice->update([
                'status' => 'waiting',
                'paid_at' => now(),
            ]);

            DB::commit();

            return redirect('client/invoices/' . $invoice->id)->with('success', 'Payment successfully.');
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with([
            /**
             * =========================================
             * RELATIONS
             * =========================================
             */
            'client',
            'service',
            'items.product',
            'payments',
            'user',
        ])->findOrFail($id);

        return Inertia::render('billings/invoices/InvoiceShowClient', [
            'title' => 'Invoice Detail',
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function downloadInvoice(
    Invoice $invoice
    ) {
        try {

            /**
             * =========================================
             * LOAD RELATIONS
             * =========================================
             */
            $invoice->load([
                'client',
                'service',
                'items',
            ]);


            /**
             * =========================================
             * GENERATE PDF
             * =========================================
             */
            $pdf = Pdf::loadView(
                'pdf.invoice',
                [
                    'invoice' => $invoice
                ]
            );


            /**
             * =========================================
             * FILE NAME
             * =========================================
             */
            $fileName =
                $invoice->invoice_number . '.pdf';


            /**
             * =========================================
             * DOWNLOAD
             * =========================================
             */
            return $pdf->download($fileName);


        } catch (\Throwable $th) {

            return back()->withErrors([
                'error' => $th->getMessage(),
            ]);

        }
    }
}
