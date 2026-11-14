<?php

namespace App\Services;

use App\Mail\VisitRequestStatusMail;
use App\Models\ClientPic;
use App\Models\VisitRequest;
use App\Models\VisitRequestMember;
use App\Models\Visitor;
use App\Models\VisitorAttemp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pengajuan kunjungan: review (approve / reject) and check-in / check-out.
 */
class VisitRequestService
{
    /*
    |--------------------------------------------------------------------------
    | REVIEW
    |--------------------------------------------------------------------------
    */
    public static function approve(VisitRequest $visit, ?string $adminNotes = null): VisitRequest
    {
        return DB::transaction(function () use ($visit, $adminNotes) {
            $visit = VisitRequest::lockForUpdate()->findOrFail($visit->id);
            self::ensureStatus($visit, ['pending'], 'disetujui');

            if ($visit->visit_date->endOfDay()->isPast()) {
                throw ValidationException::withMessages([
                    'status' => 'Tanggal kunjungan sudah lewat, pengajuan tidak bisa disetujui.',
                ]);
            }

            $visit->update([
                'status' => 'approved',
                'checkin_code' => VisitRequest::newCheckinCode(),
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'admin_notes' => $adminNotes,
                'rejection_reason' => null,
            ]);

            return $visit;
        });
    }

    public static function reject(VisitRequest $visit, string $reason): VisitRequest
    {
        return DB::transaction(function () use ($visit, $reason) {
            $visit = VisitRequest::lockForUpdate()->findOrFail($visit->id);
            self::ensureStatus($visit, ['pending', 'approved'], 'ditolak');

            if ($visit->members()->whereNotNull('checked_in_at')->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Sudah ada tamu yang check-in, pengajuan tidak bisa ditolak.',
                ]);
            }

            $visit->update([
                'status' => 'rejected',
                'checkin_code' => null,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $visit;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK-IN / CHECK-OUT
    |--------------------------------------------------------------------------
    | $byUserId = admin doing a front-desk check-in, null = self check-in.
    */
    public static function checkIn(VisitRequestMember $member, ?int $byUserId = null): Visitor
    {
        return DB::transaction(function () use ($member, $byUserId) {
            $member = VisitRequestMember::lockForUpdate()->findOrFail($member->id);
            $visit = $member->visitRequest()->with('racks.room.locationDataCenter')->firstOrFail();

            if ($visit->status !== 'approved') {
                throw ValidationException::withMessages(['checkin' => 'Pengajuan kunjungan belum disetujui atau sudah dibatalkan.']);
            }

            if (! $visit->isVisitDay()) {
                throw ValidationException::withMessages([
                    'checkin' => 'Check-in hanya bisa dilakukan pada tanggal kunjungan (' . $visit->visit_date->translatedFormat('d F Y') . ').',
                ]);
            }

            if ($member->checked_in_at) {
                throw ValidationException::withMessages(['checkin' => "{$member->name} sudah check-in."]);
            }

            $racks = $visit->racks->map(fn ($r) => $r->code ?? $r->name)->filter()->implode(', ');

            $visitor = Visitor::create([
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'address' => $member->address,
                'nik' => $member->nik,
                'company_name' => $member->company_name,
                'position_in_company' => $member->position_in_company,
                'type_of_visit' => $visit->type_of_visit,
                'visit_purpose' => Str::limit($visit->purpose . ($racks ? " (Rack: {$racks})" : ''), 250, ''),
                'visit_note' => Str::limit("Pengajuan {$visit->request_number}" . ($visit->notes ? " — {$visit->notes}" : ''), 250, ''),
                'visit_date' => $visit->visit_date,
                'checkin_time' => now(),
                'status' => 'checkin',
                'visit_request_id' => $visit->id,
                'visit_request_member_id' => $member->id,
            ]);

            $member->update([
                'visitor_id' => $visitor->id,
                'checked_in_at' => now(),
                'checked_in_by' => $byUserId,
            ]);

            return $visitor;
        });
    }

    public static function checkOut(VisitRequestMember $member): void
    {
        DB::transaction(function () use ($member) {
            $member = VisitRequestMember::lockForUpdate()->findOrFail($member->id);

            if (! $member->checked_in_at) {
                throw ValidationException::withMessages(['checkout' => "{$member->name} belum check-in."]);
            }

            if ($member->checked_out_at) {
                throw ValidationException::withMessages(['checkout' => "{$member->name} sudah check-out."]);
            }

            $member->update(['checked_out_at' => now()]);

            $member->visitor?->update([
                'checkout_time' => now(),
                'status' => 'checkout',
            ]);
        });
    }

    /**
     * Photos taken on the self check-in page (stored like the guest book).
     */
    public static function storePhotos(Visitor $visitor, array $photos): int
    {
        $stored = 0;

        foreach ($photos as $photo) {
            if (! $photo instanceof UploadedFile) {
                continue;
            }

            VisitorAttemp::create([
                'visitor_id' => $visitor->id,
                'file' => $photo->store('guest_photos', 'public'),
            ]);
            $stored++;
        }

        return $stored;
    }

    /*
    |--------------------------------------------------------------------------
    | E-MAIL TO CLIENT (approved / rejected)
    |--------------------------------------------------------------------------
    | Called after the response; failures are logged, never shown to the admin.
    */
    public static function sendStatusMail(VisitRequest $visit): void
    {
        $visit->loadMissing(['client', 'requester', 'members', 'racks.room.locationDataCenter']);

        $recipients = collect([$visit->requester?->email])
            ->merge(
                ClientPic::query()
                    ->where('client_id', $visit->client_id)
                    ->where('status', 'active')
                    ->with('user:id,email')
                    ->get()
                    ->pluck('user.email')
            )
            ->filter()
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            // recorded in Riwayat Email (failed sends are marked there)
            MailLogger::guard(fn () => Mail::to($recipients->first())
                ->cc($recipients->slice(1)->all())
                ->send(new VisitRequestStatusMail($visit)));
        } catch (\Throwable $e) {
            Log::error('Failed to send visit request status mail', [
                'visit_request_id' => $visit->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function ensureStatus(VisitRequest $visit, array $allowed, string $action): void
    {
        if (! in_array($visit->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan berstatus "' . (VisitRequest::STAGE_LABELS[$visit->status] ?? $visit->status) . "\" tidak bisa {$action}.",
            ]);
        }
    }
}
