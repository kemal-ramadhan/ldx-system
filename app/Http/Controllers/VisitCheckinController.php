<?php

namespace App\Http\Controllers;

use App\Models\VisitRequest;
use App\Models\VisitRequestMember;
use App\Services\VisitRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

/**
 * Public self check-in for an approved visit request (Buku Tamu).
 *
 * code  -> list of guests (names only, NIK masked)
 * guest -> last 4 digits of NIK -> confirm data -> check-in -> take photo
 *
 * Progress is kept in the session, so a guest can only act on the visit whose
 * code was entered in this browser, and only after the NIK check.
 */
class VisitCheckinController extends Controller
{
    private const SESSION_VISIT = 'visit_checkin.visit_id';
    private const SESSION_MEMBER = 'visit_checkin.member_id';

    /** Code form (prefilled from the QR link). */
    public function form(Request $request)
    {
        return view('guest.visit-checkin', [
            'step' => 'code',
            'code' => strtoupper((string) $request->query('code', '')),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:12']], ['code.required' => 'Masukkan kode kunjungan.']);

        $visit = VisitRequest::where('checkin_code', strtoupper(trim($request->code)))->first();

        if (! $visit) {
            throw ValidationException::withMessages(['code' => 'Kode kunjungan tidak ditemukan. Periksa kembali kode pada email persetujuan.']);
        }

        if ($visit->status !== 'approved') {
            throw ValidationException::withMessages(['code' => 'Pengajuan kunjungan ini sudah tidak aktif.']);
        }

        if (! $visit->isVisitDay()) {
            throw ValidationException::withMessages([
                'code' => 'Kode ini untuk kunjungan tanggal ' . $visit->visit_date->translatedFormat('d F Y') . '. Check-in hanya bisa pada hari tersebut.',
            ]);
        }

        $request->session()->put(self::SESSION_VISIT, $visit->id);
        $request->session()->forget(self::SESSION_MEMBER);

        return redirect()->route('kunjungan.checkin.guests');
    }

    /** Guests of the visit (names + masked NIK). */
    public function guests(Request $request)
    {
        $visit = $this->sessionVisit($request);

        return view('guest.visit-checkin', [
            'step' => 'guests',
            'visit' => $visit->load(['members', 'client:id,company_name']),
        ]);
    }

    /** Last 4 digits of the NIK unlock the guest's data. */
    public function identify(Request $request, VisitRequestMember $member)
    {
        $visit = $this->sessionVisit($request);
        abort_unless($member->visit_request_id === $visit->id, 404);

        $request->validate(
            ['nik_last4' => ['required', 'digits_between:4,4']],
            ['nik_last4.required' => 'Masukkan 4 digit terakhir NIK/Paspor.', 'nik_last4.digits_between' => 'Masukkan tepat 4 digit.']
        );

        if (! $member->nikMatchesLastFour($request->nik_last4)) {
            throw ValidationException::withMessages(['nik_last4' => '4 digit terakhir NIK/Paspor tidak cocok.'])
                ->errorBag('member_' . $member->id);
        }

        if ($member->checked_in_at) {
            return back()->with('error', "{$member->name} sudah check-in.");
        }

        $request->session()->put(self::SESSION_MEMBER, $member->id);

        return redirect()->route('kunjungan.checkin.confirm');
    }

    /** Show the pre-filled data to confirm. */
    public function confirm(Request $request)
    {
        [$visit, $member] = $this->sessionMember($request);

        return view('guest.visit-checkin', [
            'step' => 'confirm',
            'visit' => $visit->load(['racks.room.locationDataCenter', 'client:id,company_name']),
            'member' => $member,
        ]);
    }

    public function store(Request $request)
    {
        [, $member] = $this->sessionMember($request);

        $request->validate(
            ['agreement' => ['accepted']],
            ['agreement.accepted' => 'Anda harus menyetujui Peraturan dan Kebijakan Keamanan.']
        );

        $visitor = VisitRequestService::checkIn($member);

        $request->session()->forget(self::SESSION_MEMBER);

        // photo page of the guest book, through a signed (tamper-proof) URL
        return redirect(URL::temporarySignedRoute('daftar.buku.tamu.lampiran', now()->addMinutes(30), ['id' => $visitor->id]))
            ->with('success', 'Check-in berhasil. Silakan ambil foto.');
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION HELPERS
    |--------------------------------------------------------------------------
    */
    private function sessionVisit(Request $request): VisitRequest
    {
        $visit = VisitRequest::find($request->session()->get(self::SESSION_VISIT));

        if (! $visit || ! $visit->canCheckInToday()) {
            $request->session()->forget([self::SESSION_VISIT, self::SESSION_MEMBER]);
            abort(redirect()->route('kunjungan.checkin')->withErrors(['code' => 'Sesi check-in berakhir. Masukkan kode kunjungan kembali.']));
        }

        return $visit;
    }

    private function sessionMember(Request $request): array
    {
        $visit = $this->sessionVisit($request);
        $member = VisitRequestMember::where('visit_request_id', $visit->id)->find($request->session()->get(self::SESSION_MEMBER));

        if (! $member) {
            abort(redirect()->route('kunjungan.checkin.guests'));
        }

        return [$visit, $member];
    }
}
