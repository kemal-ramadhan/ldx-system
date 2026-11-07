<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Services\TaxCalculator;
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

            if ($request->boolean('is_direct_payment')) {
                $this->processPaymentSuccess($invoice);
            }

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

            $this->processPaymentSuccess($invoice);


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
    public function delete(string $id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return back()->with('success', 'Invoice deleted successfully.');
    }

    private function processPaymentSuccess(Invoice $invoice)
    {
        $this->activateServiceOnFirstPayment($invoice);

        /**
         * =========================================
         * GENERATE PDF FOR PAYMENT RECEIVED
         * =========================================
         */
        $invoice->load([
            'client',
            'service',
            'items',
        ]);

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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.invoice',
            [
                'invoice' => $invoice,
                'logoSrc' => $logoSrc,
                'logoLdxSrc' => $logoLdxSrc,
            ]
        );

        $fileName =
            $invoice->invoice_number . '.pdf';

        $filePath =
            storage_path(
                'app/public/invoices/' . $fileName
            );

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

        file_put_contents(
            $filePath,
            $pdf->output()
        );

        /**
         * =========================================
         * SEND PAYMENT RECEIVED EMAIL
         * =========================================
         */
        if ($invoice->client && $invoice->client->company_email) {
            \Illuminate\Support\Facades\Mail::to(
                $invoice->client->company_email
            )->send(
                new \App\Mail\PaymentReceivedMail(
                    $invoice,
                    $filePath
                )
            );
        }
    }

    /**
     * =========================================
     * ACTIVATE SERVICE ON FIRST PAYMENT
     * =========================================
     * Saat invoice pertama dari sebuah service dibayar,
     * service yang masih berstatus "pending" otomatis
     * diaktifkan. Service yang sudah aktif / status lain
     * (suspended, terminated, dll) tidak diubah.
     */
    private function activateServiceOnFirstPayment(Invoice $invoice): void
    {
        if (!$invoice->service_id) {
            return;
        }

        $service = \App\Models\Service::query()
            ->whereKey($invoice->service_id)
            ->lockForUpdate()
            ->first();

        if (!$service || $service->status !== 'pending') {
            return;
        }

        $hasPreviousPaidInvoice = Invoice::query()
            ->where('service_id', $service->id)
            ->where('id', '!=', $invoice->id)
            ->where('status', 'paid')
            ->exists();

        if ($hasPreviousPaidInvoice) {
            return;
        }

        $service->update([
            'status' => 'active',
        ]);

        $invoice->setRelation('service', $service);
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
            'can_edit' => self::isEditable($invoice),
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
     * Statuses whose invoice can still be corrected.
     * paid / waiting (proof uploaded) / processed / cancelled are locked.
     */
    public const EDITABLE_STATUSES = ['draft', 'pending', 'sent', 'overdue', 'rejected'];

    public static function isEditable(Invoice $invoice): bool
    {
        return in_array($invoice->status, self::EDITABLE_STATUSES, true);
    }

    /**
     * =========================================
     * EDIT INVOICE
     * =========================================
     */
    public function edit(string $id)
    {
        $invoice = Invoice::with([
            'client',
            'service',
            'items.product',
        ])->findOrFail($id);

        if (!self::isEditable($invoice)) {
            return redirect("/admin/invoices/{$invoice->id}")
                ->with('error', "Invoice with status \"{$invoice->status}\" cannot be edited.");
        }

        return Inertia::render('billings/invoices/InvoiceEdit', [
            'title' => 'Edit Invoice',
            'invoice' => $invoice,
            'products' => Product::query()
                ->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get([
                    'id',
                    'name',
                    'base_price',
                    'unit',
                    'billing_type',
                ]),
        ]);
    }

    /**
     * =========================================
     * UPDATE INVOICE
     * =========================================
     * Invoice number, client & service stay as they are.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.ppn_enabled' => 'nullable|boolean',
            'items.*.ppn_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.pph23_enabled' => 'nullable|boolean',
            'items.*.pph23_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            // lock so a payment confirmed meanwhile is not overwritten
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($id);

            if (!self::isEditable($invoice)) {
                DB::rollBack();

                return redirect("/admin/invoices/{$invoice->id}")
                    ->with('error', "Invoice with status \"{$invoice->status}\" cannot be edited.");
            }

            $productNames = Product::query()
                ->whereIn('id', collect($validated['items'])->pluck('product_id'))
                ->pluck('name', 'id');

            $items = collect($validated['items'])->map(function ($item) use ($productNames) {
                $total = (float) $item['quantity'] * (float) $item['price'];

                return [
                    'product_id' => $item['product_id'],
                    'name' => $productNames[$item['product_id']] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $total,
                ] + TaxCalculator::item(
                    $total,
                    filter_var($item['ppn_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    $item['ppn_percentage'] ?? null,
                    filter_var($item['pph23_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    $item['pph23_percentage'] ?? null,
                );
            });

            $summary = TaxCalculator::summary($items, 'total');

            $status = $invoice->status;

            // new due date in the future -> no longer overdue
            if (
                $status === 'overdue' &&
                \Illuminate\Support\Carbon::parse($validated['due_date'])->startOfDay()->gte(today())
            ) {
                $status = 'sent';
            }

            $invoice->update([
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'subtotal' => $summary['subtotal'],
                'ppn_enabled' => $summary['ppn_enabled'],
                'ppn_percentage' => $summary['ppn_percentage'],
                'ppn_amount' => $summary['ppn_amount'],
                'pph23_enabled' => $summary['pph23_enabled'],
                'pph23_percentage' => $summary['pph23_percentage'],
                'pph23_amount' => $summary['pph23_amount'],
                'total' => $summary['total'],
                'status' => $status,
            ]);

            $invoice->items()->delete();

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->withErrors([
                'error' => $th->getMessage(),
            ]);
        }

        // ?edited=1 opens the "resend to client?" popup on the detail page
        return redirect("/admin/invoices/{$invoice->id}?edited=1");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $invoice = Invoice::with(['payments', 'service'])->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->withErrors([
                'error' => 'Paid invoices cannot be deleted.',
            ]);
        }

        DB::beginTransaction();

        try {
            /**
             * =========================================
             * ROLL BACK SERVICE NEXT DUE DATE
             * =========================================
             * Generating an invoice moves the service due date
             * one cycle ahead, so undo that when the latest
             * invoice of the service is deleted.
             */
            $service = $invoice->service;

            if ($service && $invoice->due_date) {
                $isLatestInvoice = !Invoice::where('service_id', $service->id)
                    ->where('id', '>', $invoice->id)
                    ->exists();

                if ($isLatestInvoice) {
                    $service->update([
                        'next_due_date' => $invoice->due_date,
                    ]);
                }
            }

            $files = $invoice->payments
                ->pluck('proof_of_payment')
                ->filter()
                ->push('invoices/' . $invoice->invoice_number . '.pdf');

            // items & payments are removed by cascade
            $invoice->delete();

            DB::commit();

            Storage::disk('public')->delete($files->all());
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Failed to delete invoice: ' . $th->getMessage(),
            ]);
        }

        // deleted from the detail page -> back to the list
        if (str_contains(url()->previous(), "/admin/invoices/{$invoice->id}")) {
            return redirect('/admin/invoices')->with('success', 'Invoice deleted successfully.');
        }

        return back()->with('success', 'Invoice deleted successfully.');
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
            self::deliverInvoiceEmail($invoice);

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
     * GENERATE PDF, EMAIL CLIENT & MARK AS SENT
     * =========================================
     */
    public static function deliverInvoiceEmail(
        Invoice $invoice
    ): void {
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

            $this->processPaymentSuccess($invoice);

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
