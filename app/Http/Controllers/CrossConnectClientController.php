<?php

namespace App\Http\Controllers;

use App\Models\CrossConnect;
use App\Support\ClientPortAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Cross connects in the client portal.
 * Only cross connects with at least one end on a port owned by the client.
 * Read-only, plus a terminate request that the LDX team (admin) processes.
 */
class CrossConnectClientController extends Controller
{
    private function clientId(): int
    {
        $clientPic = Auth::user()->clientPic;

        abort_unless(
            $clientPic && $clientPic->client_id,
            403,
            'User belum terhubung dengan client.'
        );

        return (int) $clientPic->client_id;
    }

    private function baseQuery(int $clientId, bool $withLocation = false)
    {
        $query = CrossConnect::query()->with([
            ...ClientPortAccess::portRelations($withLocation),
            'interconnectionRequest:id,request_number,requester_client_id',
        ]);

        return ClientPortAccess::touchingClient($query, $clientId);
    }

    /**
     * =========================================================
     * INDEX
     * =========================================================
     */
    public function index(Request $request)
    {
        $clientId = $this->clientId();

        $query = $this->baseQuery($clientId)->latest('id');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('cross_connect_number', 'like', "%{$search}%")
                    ->orWhereHas('interconnectionRequest', fn ($r) => $r->where('request_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $crossConnects = $query->paginate(15)->withQueryString();

        $crossConnects->getCollection()->transform(function ($item) use ($clientId) {
            $item->setAttribute('my_side', ClientPortAccess::sideOf($item, $clientId));

            return $item;
        });

        // statistics are scoped to the client too
        $counts = ClientPortAccess::touchingClient(CrossConnect::query(), $clientId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Client/CrossConnects/Index', [
            'crossConnects' => $crossConnects,
            'statistics' => [
                'total' => (int) $counts->sum(),
                'active' => (int) ($counts['active'] ?? 0),
                'planned' => (int) ($counts['planned'] ?? 0),
                'terminated' => (int) ($counts['terminated'] ?? 0),
                'cancelled' => (int) ($counts['cancelled'] ?? 0),
            ],
            'filters' => [
                'status' => $request->status ?? 'all',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * =========================================================
     * SHOW (404 when the cross connect is not the client's)
     * =========================================================
     */
    public function show(string $crossConnect)
    {
        $clientId = $this->clientId();

        $record = $this->baseQuery($clientId, withLocation: true)
            ->with('terminateRequester:id,name')
            ->whereKey($crossConnect)
            ->firstOrFail();

        return Inertia::render('Client/CrossConnects/Show', [
            'crossConnect' => $record,
            'mySide' => ClientPortAccess::sideOf($record, $clientId),
        ]);
    }

    /**
     * =========================================================
     * REQUEST TERMINATE (processed by admin)
     * =========================================================
     */
    public function requestTerminate(Request $request, string $crossConnect)
    {
        $clientId = $this->clientId();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Alasan terminate wajib diisi.',
            'reason.max' => 'Alasan terminate maksimal 2000 karakter.',
        ]);

        DB::transaction(function () use ($clientId, $crossConnect, $validated) {
            $record = $this->baseQuery($clientId)
                ->whereKey($crossConnect)
                ->lockForUpdate()
                ->firstOrFail();

            if ($record->status !== 'active') {
                throw ValidationException::withMessages([
                    'reason' => 'Hanya cross connect yang aktif yang bisa diajukan terminate.',
                ]);
            }

            if ($record->terminate_requested_at) {
                throw ValidationException::withMessages([
                    'reason' => 'Permintaan terminate sudah diajukan dan sedang menunggu diproses tim LDX.',
                ]);
            }

            $record->update([
                'terminate_requested_at' => now(),
                'terminate_requested_by' => Auth::id(),
                'terminate_request_reason' => $validated['reason'],
            ]);
        });

        return back()->with('success', 'Permintaan terminate berhasil dikirim. Tim LDX akan memprosesnya.');
    }
}
