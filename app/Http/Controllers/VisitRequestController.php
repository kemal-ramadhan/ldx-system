<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\VisitRequest;
use App\Models\VisitRequestMember;
use App\Services\VisitRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Pengajuan kunjungan — admin: review, approve / reject, front-desk check-in.
 */
class VisitRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = VisitRequest::query()
            ->with(['client:id,company_name', 'requester:id,name', 'members:id,visit_request_id,checked_in_at'])
            ->withCount('members');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('request_number', 'like', "%{$search}%")
                ->orWhere('purpose', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($c) => $c->where('company_name', 'like', "%{$search}%"))
                ->orWhereHas('members', fn ($m) => $m->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")));
        }

        // pending first, then the nearest visit date
        $visits = $query
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('visit_date', 'desc')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('visit-requests/Index', [
            'title' => 'Pengajuan Kunjungan',
            'visits' => $visits,
            'summary' => [
                'pending' => VisitRequest::where('status', 'pending')->count(),
                'today' => VisitRequest::where('status', 'approved')->whereDate('visit_date', today())->count(),
                'visiting' => VisitRequestMember::whereNotNull('checked_in_at')->whereNull('checked_out_at')->count(),
                'rejected' => VisitRequest::where('status', 'rejected')->count(),
            ],
            'clients' => Client::orderBy('company_name')->get(['id', 'company_name']),
            'filters' => $request->only(['status', 'client_id', 'date', 'search']),
        ]);
    }

    public function show(VisitRequest $visitRequest)
    {
        $visitRequest->load([
            'client:id,company_code,company_name,company_email,company_phone',
            'requester:id,name,email,phone',
            'requester.clientPic:id,user_id,position',
            'reviewer:id,name',
            'racks.room.locationDataCenter',
            'members.checkedInBy:id,name',
        ]);

        return Inertia::render('visit-requests/Show', [
            'title' => $visitRequest->request_number,
            'visit' => $visitRequest,
            'canCheckIn' => $visitRequest->canCheckInToday(),
        ]);
    }

    public function approve(Request $request, VisitRequest $visitRequest)
    {
        $data = $request->validate(['admin_notes' => ['nullable', 'string', 'max:2000']]);

        $visit = VisitRequestService::approve($visitRequest, $data['admin_notes'] ?? null);

        dispatch(fn () => VisitRequestService::sendStatusMail($visit->fresh()))->afterResponse();

        return back()->with('success', "Pengajuan disetujui. Kode kunjungan {$visit->checkin_code} dikirim ke email client.");
    }

    public function reject(Request $request, VisitRequest $visitRequest)
    {
        $data = $request->validate(
            ['rejection_reason' => ['required', 'string', 'max:2000']],
            ['rejection_reason.required' => 'Alasan penolakan wajib diisi.']
        );

        $visit = VisitRequestService::reject($visitRequest, $data['rejection_reason']);

        dispatch(fn () => VisitRequestService::sendStatusMail($visit->fresh()))->afterResponse();

        return back()->with('success', 'Pengajuan ditolak. Client diberi tahu melalui email.');
    }

    public function checkIn(VisitRequest $visitRequest, VisitRequestMember $member)
    {
        abort_unless($member->visit_request_id === $visitRequest->id, 404);

        VisitRequestService::checkIn($member, Auth::id());

        return back()->with('success', "{$member->name} berhasil check-in.");
    }

    public function checkOut(VisitRequest $visitRequest, VisitRequestMember $member)
    {
        abort_unless($member->visit_request_id === $visitRequest->id, 404);

        VisitRequestService::checkOut($member);

        return back()->with('success', "{$member->name} berhasil check-out.");
    }
}
