<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\VisitRequest;
use App\Services\MailResender;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Riwayat Email (admin only): every e-mail sent by the app, its status,
 * an inbox-like preview and "Kirim Ulang".
 */
class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::query()
            ->with(['client:id,company_name', 'triggeredBy:id,name'])
            ->withCount('resends')
            ->latest('id');

        $unconfirmedBefore = now()->subMinutes(EmailLog::UNCONFIRMED_AFTER_MINUTES);

        match ($request->status) {
            'sent', 'failed' => $query->where('status', $request->status),
            'failed_unresolved' => $query->unresolvedFailures(),
            'sending' => $query->where('status', 'sending')->where('created_at', '>=', $unconfirmedBefore),
            'unconfirmed' => $query->where('status', 'sending')->where('created_at', '<', $unconfirmedBefore),
            default => null,
        };

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // e.g. ?related=invoice:12 (link from the invoice page)
        if ($request->filled('related') && preg_match('/^(invoice|visit):(\d+)$/', $request->related, $m)) {
            $query->where('related_type', $m[1] === 'invoice' ? Invoice::class : VisitRequest::class)
                ->where('related_id', $m[2]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('subject', 'like', "%{$search}%")
                ->orWhere('to', 'like', "%{$search}%")
                ->orWhere('cc', 'like', "%{$search}%")
                ->orWhere('context', 'like', "%{$search}%"));
        }

        return Inertia::render('email-logs/Index', [
            'title' => 'Riwayat Email',
            'logs' => $query->paginate(20)->withQueryString(),
            'summary' => [
                'sent_today' => EmailLog::where('status', 'sent')->whereDate('sent_at', today())->count(),
                'sent' => EmailLog::where('status', 'sent')->count(),
                'failed' => EmailLog::where('status', 'failed')->count(),
                'failed_unresolved' => EmailLog::unresolvedFailures()->count(),
                'pending' => EmailLog::where('status', 'sending')->count(),
            ],
            'types' => EmailLog::TYPES,
            'filters' => $request->only(['status', 'type', 'date_from', 'date_to', 'search', 'related']),
            'archiveBcc' => AppSetting::archiveBcc(),
            'archiveBccMeta' => AppSetting::with('updater:id,name')
                ->where('key', AppSetting::ARCHIVE_BCC)
                ->first(['id', 'key', 'updated_by', 'updated_at']),
        ]);
    }

    public function show(EmailLog $emailLog)
    {
        $emailLog->load([
            'client:id,company_name',
            'triggeredBy:id,name',
            'resentFrom:id,status,created_at,sent_at',
            'resends' => fn ($q) => $q->select(['id', 'resent_from_id', 'status', 'created_at', 'sent_at', 'error_message', 'triggered_by'])
                ->with('triggeredBy:id,name'),
        ]);

        return Inertia::render('email-logs/Show', [
            'title' => $emailLog->subject ?? 'Email',
            'log' => $emailLog,
            'relatedUrl' => match ($emailLog->related_type) {
                Invoice::class => "/admin/invoices/{$emailLog->related_id}",
                VisitRequest::class => "/admin/visit-requests/{$emailLog->related_id}",
                default => null,
            },
            'canResend' => $emailLog->type !== 'other' && $emailLog->related_id,
        ]);
    }

    /**
     * Raw HTML for the sandboxed preview iframe. Scripts are blocked twice:
     * the iframe has sandbox without allow-scripts, and this CSP forbids them.
     */
    public function preview(EmailLog $emailLog)
    {
        return response($emailLog->html_body ?? '<p style="font-family:sans-serif;color:#6b7280">Isi email tidak tersimpan.</p>')
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Security-Policy', "default-src 'none'; img-src data: https: http:; style-src 'unsafe-inline'; font-src data: https:")
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * =========================================================
     * PENGATURAN BCC ARSIP (stored in the database, not .env)
     * =========================================================
     */
    public function updateSettings(Request $request)
    {
        // trim + lowercase before validating (also when called without the TrimStrings middleware)
        $request->merge([
            'archive_bcc' => collect((array) $request->input('archive_bcc', []))
                ->map(fn ($address) => strtolower(trim((string) $address)))
                ->filter()
                ->values()
                ->all(),
        ]);

        $data = $request->validate([
            'archive_bcc' => ['present', 'array', 'max:10'],
            'archive_bcc.*' => ['required', 'email', 'max:255', 'distinct:ignore_case'],
        ], [
            'archive_bcc.max' => 'Maksimal 10 alamat email BCC.',
            'archive_bcc.*.email' => 'Format email tidak valid: :input',
            'archive_bcc.*.distinct' => 'Email :input dimasukkan lebih dari sekali.',
        ]);

        $list = collect($data['archive_bcc'])->map(fn ($a) => strtolower(trim($a)))->unique()->values()->all();

        AppSetting::put(AppSetting::ARCHIVE_BCC, $list);

        return back()->with('success', $list
            ? 'Pengaturan BCC arsip disimpan (' . count($list) . ' alamat).'
            : 'BCC arsip dinonaktifkan.');
    }

    public function resend(EmailLog $emailLog)
    {
        $new = MailResender::resend($emailLog);

        return redirect("/admin/email-logs/{$new->id}")
            ->with('success', 'Email berhasil dikirim ulang.');
    }
}
