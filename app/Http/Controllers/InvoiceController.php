<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $invoices = Invoice::with([
            'client',
            'service',
        ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'invoice_number',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhereHas('client', function ($client) use ($search) {

                            $client->where(
                                'company_name',
                                'like',
                                "%{$search}%"
                            );
                        });
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
            'billings/invoices/Invoices',
            [
                'title' => 'Invoices Management',
                'invoices' => $invoices,
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                ],
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
            'paid_at' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'proof' => 'nullable|image|max:2048',
            'is_direct_payment' => 'required|boolean',
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

                'amount' =>
                $request->amount,
                'payment_date' =>
                $request->paid_at,
                'payment_number' =>
                $request->reference_number,
                'payment_reference' =>
                $request->reference_number,

                'notes' =>
                $request->notes,

                'proof_of_payment' =>
                $proofPath,

                /**
                 * =========================================
                 * DIRECT PAYMENT
                 * =========================================
                 */
                'status' =>
                $request->boolean('is_direct_payment')
                    ? 'completed'
                    : 'pending',

                'verified_by' => Auth::id(),
                'verified_at' =>
                $request->boolean('is_direct_payment')
                    ? now()
                    : null,
            ]);

            /**
             * =========================================
             * UPDATE INVOICE
             * =========================================
             */
            $invoice->update([

                /**
                 * =========================================
                 * SALES PAYMENT
                 * =========================================
                 */
                'status' =>
                $request->boolean('is_direct_payment')
                    ? 'paid'
                    : 'waiting',

                'paid_at' =>
                $request->boolean('is_direct_payment')
                    ? now()
                    : null,
            ]);

            DB::commit();

            return back()
                ->with(
                    'success',
                    $request->boolean('is_direct_payment')
                        ? 'Payment confirmed successfully.'
                        : 'Payment uploaded successfully.'
                );
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function verifyPayment(
        Invoice $invoice
    ) {

        DB::beginTransaction();

        try {

            /**
             * =========================================
             * GET PAYMENT
             * =========================================
             */
            $payment = $invoice->payments()->first();


            if (!$payment) {
                return back()->withErrors([
                    'error' => 'Payment data not found.'
                ]);
            }


            /**
             * =========================================
             * UPDATE PAYMENT
             * =========================================
             */
            $payment->update([

                'status' =>
                'completed',

                'verified_by' =>
                Auth::id(),

                'verified_at' =>
                now(),

            ]);


            /**
             * =========================================
             * UPDATE INVOICE
             * =========================================
             */
            $invoice->update([

                'status' =>
                'paid',

                'paid_at' =>
                now(),

            ]);


            DB::commit();


            return back()->with(
                'success',
                'Payment verified successfully.'
            );
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

        return Inertia::render('billings/invoices/InvoiceShow', [
            'title' => 'Invoice Detail',
            'invoice' => $invoice,
        ]);
    }

    /**
     * =========================================
     * SHOW PAYMENT PAGE
     * =========================================
     */
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
            'billings/payments/Payments',
            [
                'title' => 'Payment Confirmation',
                'invoice' => $invoice,
            ]
        );
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

    /**
     * =========================================
     * SEND INVOICE EMAIL
     * =========================================
     */
    public function sendInvoice(
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
             * LOAD LOGO
             * =========================================
             */
            $logoPath = public_path('assets/logos/ldx-logo.png');
            $logoLdx = public_path('assets/logos/ldx.png');

            $logoData = base64_encode(
                file_get_contents($logoPath)
            );

            $logoDataLdx = base64_encode(
                file_get_contents($logoLdx)
            );

            $logoSrc = 'data:image/png;base64,' . $logoData;
            $logoLdxSrc = 'data:image/png;base64,' . $logoDataLdx;

            /**
             * =========================================
             * GENERATE PDF
             * =========================================
             */
            $pdf = Pdf::loadView(
                'pdf.invoice',
                [
                    'invoice' => $invoice,
                    'logoSrc' => $logoSrc,
                    'logoLdxSrc' => $logoLdxSrc,
                ]
            );

            /**
             * =========================================
             * PDF FILE NAME
             * =========================================
             */
            $fileName =
                $invoice->invoice_number . '.pdf';

            /**
             * =========================================
             * PDF STORAGE PATH
             * =========================================
             */
            $filePath =
                storage_path(
                    'app/public/invoices/' . $fileName
                );

            /**
             * =========================================
             * CREATE DIRECTORY
             * =========================================
             */
            if (
                !file_exists(
                    storage_path('app/public/invoices')
                )
            ) {

                mkdir(
                    storage_path('app/public/invoices'),
                    0777,
                    true
                );
            }

            /**
             * =========================================
             * SAVE PDF
             * =========================================
             */
            file_put_contents(
                $filePath,
                $pdf->output()
            );

            /**
             * =========================================
             * SEND EMAIL
             * =========================================
             */
            Mail::to(
                $invoice->client->company_email
            )->send(
                new InvoiceMail(
                    $invoice,
                    $filePath
                )
            );

            /**
             * =========================================
             * UPDATE STATUS
             * =========================================
             */
            if (
                in_array($invoice->status, ['draft', 'pending'])
            ) {
                $invoice->update([
                    'status' => 'sent',
                ]);
            }

            return back()->with(
                'success',
                'Invoice email sent successfully.'
            );
        } catch (\Throwable $th) {
            return back()->withErrors([
                'error' => $th->getMessage(),
            ]);
        }
    }

    /**
     * =========================================
     * APPROVE PAYMENT
     * =========================================
     */
    public function approvePayment(
        Invoice $invoice
    ) {
        DB::beginTransaction();

        try {

            /**
             * =========================================
             * UPDATE INVOICE
             * =========================================
             */
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Payment approved successfully.'
            );
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $th->getMessage(),
            ]);
        }
    }

    /**
     * =========================================
     * REJECT PAYMENT
     * =========================================
     */
    public function rejectPayment(
        Request $request,
        Invoice $invoice
    ) {
        DB::beginTransaction();

        try {

            /**
             * =========================================
             * UPDATE STATUS BACK TO SENT
             * =========================================
             */
            $invoice->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Payment rejected successfully.'
            );
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $th->getMessage(),
            ]);
        }
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

            $logoPath = public_path('assets/logos/ldx-logo.png');
            $logoLdx = public_path('assets/logos/ldx.png');
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoDataLdx = base64_encode(file_get_contents($logoLdx));
            $logoSrc  = 'data:image/png;base64,' . $logoData;
            $logoLdxSrc  = 'data:image/png;base64,' . $logoDataLdx;


            /**
             * =========================================
             * GENERATE PDF
             * =========================================
             */
            $pdf = Pdf::loadView(
                'pdf.invoice',
                [
                    'invoice' => $invoice,
                    'logoSrc' => $logoSrc,
                    'logoLdxSrc' => $logoLdxSrc,
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
