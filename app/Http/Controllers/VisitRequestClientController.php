<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Rack;
use App\Models\VisitRequest;
use App\Support\VisitQr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Pengajuan kunjungan — client portal. Everything is scoped to the client
 * of the logged-in PIC; other clients' requests are 404.
 */
class VisitRequestClientController extends Controller
{
    private function clientId(): int
    {
        $clientPic = Auth::user()->clientPic;

        abort_unless($clientPic && $clientPic->client_id, 403, 'User belum terhubung dengan client.');

        return (int) $clientPic->client_id;
    }

    private function findOwn(string $id): VisitRequest
    {
        return VisitRequest::where('client_id', $this->clientId())->findOrFail($id);
    }

    /** Racks the client rents (active rentals) — the only racks it may visit. */
    private function rentedRacks(int $clientId)
    {
        return Rack::query()
            ->whereHas('clientRacks', fn ($q) => $q->where('client_id', $clientId)->where('status', 'active'))
            ->with('room:id,location_data_center_id,name', 'room.locationDataCenter:id,name')
            ->orderBy('code')
            ->get(['id', 'room_id', 'code', 'name']);
    }

    private function rules(int $clientId): array
    {
        return [
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'type_of_visit' => ['required', Rule::in(array_keys(VisitRequest::TYPES))],
            'purpose' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'rack_ids' => ['nullable', 'array'],
            'rack_ids.*' => [
                'integer',
                Rule::in($this->rentedRacks($clientId)->pluck('id')->all()),
            ],

            'members' => ['required', 'array', 'min:1', 'max:20'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.nik' => ['required', 'string', 'max:20'],
            'members.*.phone' => ['required', 'string', 'max:20'],
            'members.*.email' => ['required', 'email', 'max:255'],
            'members.*.address' => ['required', 'string', 'max:255'],
            'members.*.company_name' => ['required', 'string', 'max:255'],
            'members.*.position_in_company' => ['required', 'string', 'max:255'],
        ];
    }

    private function messages(): array
    {
        return [
            'visit_date.after_or_equal' => 'Tanggal kunjungan minimal hari ini.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'rack_ids.*.in' => 'Rack yang dipilih bukan rack sewaan perusahaan Anda.',
            'members.required' => 'Tambahkan minimal 1 tamu.',
            'members.max' => 'Maksimal 20 tamu per pengajuan.',
            'members.*.*.required' => 'Data tamu wajib diisi lengkap.',
            'members.*.email.email' => 'Format email tamu tidak valid.',
        ];
    }

    /**
     * =========================================================
     * INDEX
     * =========================================================
     */
    public function index(Request $request)
    {
        $clientId = $this->clientId();

        $visits = VisitRequest::query()
            ->where('client_id', $clientId)
            ->with('members:id,visit_request_id,checked_in_at')
            ->withCount('members')
            ->when($request->filled('status') && $request->status !== 'all', fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($s) => $s
                ->where('request_number', 'like', "%{$request->search}%")
                ->orWhere('purpose', 'like', "%{$request->search}%")))
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Client/VisitRequests/Index', [
            'visits' => $visits,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * =========================================================
     * CREATE / STORE
     * =========================================================
     */
    public function create()
    {
        $clientId = $this->clientId();

        return Inertia::render('Client/VisitRequests/Create', [
            'racks' => $this->rentedRacks($clientId),
            'types' => VisitRequest::TYPES,
            'companyName' => Client::whereKey($clientId)->value('company_name'),
        ]);
    }

    public function store(Request $request)
    {
        $clientId = $this->clientId();
        $data = $request->validate($this->rules($clientId), $this->messages());

        $visit = DB::transaction(function () use ($data, $clientId) {
            $visit = VisitRequest::create([
                'request_number' => VisitRequest::nextNumber(),
                'client_id' => $clientId,
                'requested_by' => Auth::id(),
                'visit_date' => $data['visit_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'type_of_visit' => $data['type_of_visit'],
                'purpose' => $data['purpose'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            $visit->racks()->sync($data['rack_ids'] ?? []);
            $visit->members()->createMany($data['members']);

            return $visit;
        });

        return redirect("/client/visit-requests/{$visit->id}")
            ->with('success', 'Pengajuan kunjungan terkirim. Tim LDX akan meninjau pengajuan Anda.');
    }

    /**
     * =========================================================
     * SHOW
     * =========================================================
     */
    public function show(string $id)
    {
        $visit = $this->findOwn($id)->load([
            'members',
            'racks.room.locationDataCenter',
            'requester:id,name,email',
            'reviewer:id,name',
        ]);

        $qr = $visit->status === 'approved' && $visit->checkin_code
            ? ['svg' => VisitQr::svg(VisitQr::checkinUrl($visit)), 'url' => VisitQr::checkinUrl($visit)]
            : null;

        return Inertia::render('Client/VisitRequests/Show', [
            'visit' => $visit,
            'qr' => $qr,
            'canEdit' => $visit->isEditable(),
            'canCancel' => $visit->isCancellable(),
        ]);
    }

    /**
     * =========================================================
     * EDIT / UPDATE (only while pending)
     * =========================================================
     */
    public function edit(string $id)
    {
        $visit = $this->findOwn($id)->load(['members', 'racks:id']);

        if (! $visit->isEditable()) {
            return redirect("/client/visit-requests/{$visit->id}")
                ->with('error', 'Pengajuan yang sudah ditinjau tidak bisa diubah.');
        }

        return Inertia::render('Client/VisitRequests/Create', [
            'visit' => $visit,
            'racks' => $this->rentedRacks($visit->client_id),
            'types' => VisitRequest::TYPES,
            'companyName' => Client::whereKey($visit->client_id)->value('company_name'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $visit = $this->findOwn($id);

        if (! $visit->isEditable()) {
            return redirect("/client/visit-requests/{$visit->id}")
                ->with('error', 'Pengajuan yang sudah ditinjau tidak bisa diubah.');
        }

        $data = $request->validate($this->rules($visit->client_id), $this->messages());

        DB::transaction(function () use ($visit, $data) {
            $visit->update([
                'visit_date' => $data['visit_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'type_of_visit' => $data['type_of_visit'],
                'purpose' => $data['purpose'],
                'notes' => $data['notes'] ?? null,
            ]);

            $visit->racks()->sync($data['rack_ids'] ?? []);
            $visit->members()->delete();
            $visit->members()->createMany($data['members']);
        });

        return redirect("/client/visit-requests/{$visit->id}")->with('success', 'Pengajuan kunjungan diperbarui.');
    }

    /**
     * =========================================================
     * CANCEL
     * =========================================================
     */
    public function cancel(string $id)
    {
        $visit = $this->findOwn($id)->load('members');

        if (! $visit->isCancellable()) {
            return back()->with('error', 'Pengajuan ini tidak bisa dibatalkan.');
        }

        $visit->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'checkin_code' => null,
        ]);

        return back()->with('success', 'Pengajuan kunjungan dibatalkan.');
    }
}
