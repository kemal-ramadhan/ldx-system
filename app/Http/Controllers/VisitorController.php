<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\VisitorAttemp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class VisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $visitors = Visitor::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('visitors/Visitors', [
            'title' => 'Visitor Management',
            'visitors' => $visitors,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function guestChoice()
    {
        return view('guest.choices');
    }

    public function guest(Request $request)
    {
        // card chosen on /buku-tamu -> preselect "Tujuan Kunjungan"
        $defaultType = [
            'berkunjung' => 'Kunjungan',
            'perawatan' => 'maintenance',
            'lainnya' => 'lainnya',
        ][$request->query('jenis')] ?? null;

        return view('guest.index', compact('defaultType'));
    }
    
    public function guestLampiran($id)
    {
        $guest = Visitor::findOrFail($id);

        // upload endpoint signed the same way (valid as long as this page)
        $uploadUrl = URL::temporarySignedRoute('buku.tamu.lampiran.store', now()->addMinutes(30), ['id' => $guest->id]);

        return view('guest.lampiran', compact('guest', 'uploadUrl'));
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

    public function storeGuest(Request $request)
    {
        // max lengths follow the visitors table columns (string = 255)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'nik' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'position_in_company' => 'required|string|max:255',
            'type_of_visit' => 'required|string|max:255',
            'visit_purpose' => 'required|string|max:255',
            'visit_note' => 'nullable|string|max:255',
            'visit_date' => 'required|date',
            'agreement' => 'accepted',
        ], [
            'required' => ':attribute wajib diisi.',
            'email' => 'Format :attribute tidak valid (contoh: nama@example.com).',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute bukan tanggal yang valid.',
            'accepted' => 'Anda harus menyetujui Peraturan dan Kebijakan Keamanan.',
        ], [
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'phone' => 'Nomor Telepon',
            'nik' => 'Identitas (KTP/Paspor)',
            'address' => 'Alamat',
            'company_name' => 'Nama Perusahaan',
            'position_in_company' => 'Posisi/Jabatan',
            'type_of_visit' => 'Tujuan Kunjungan',
            'visit_purpose' => 'Area yang Dikunjungi',
            'visit_note' => 'Keperluan Khusus / Catatan',
            'visit_date' => 'Tanggal Kunjungan',
            'agreement' => 'Persetujuan',
        ]);

        unset($validated['agreement']);

        try {
            $guest = Visitor::create([
                ...$validated,
                'checkin_time' => Carbon::now(),
                'status' => 'checkin',
            ]);
        } catch (\Throwable $e) {
            report($e);

            // keep what the visitor typed so they don't have to refill
            return back()
                ->withInput()
                ->withErrors(['general' => 'Data gagal disimpan, silakan coba kirim ulang.']);
        }

        // signed URL: the photo step can't be opened for another visitor id
        return redirect(URL::temporarySignedRoute('daftar.buku.tamu.lampiran', now()->addMinutes(30), ['id' => $guest->id]))
            ->with('success', 'Data berhasil disimpan');
    }

    public function storeLampiran(Request $request, $id)
    {
        $request->validate([
            'photos' => ['required', 'array', 'max:5'],
            'photos.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'photos.required' => 'Ambil minimal 1 foto.',
            'photos.*.image' => 'File harus berupa foto.',
            'photos.*.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $visitor = Visitor::findOrFail($id);

        try {
            foreach ($request->file('photos') as $photo) {
                VisitorAttemp::create([
                    'visitor_id' => $visitor->id,
                    'file' => $photo->store('guest_photos', 'public'),
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            // never expose internal errors on a public page
            return response()->json([
                'success' => false,
                'message' => 'Foto gagal disimpan, silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('buku.tamu'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $visitor = Visitor::with(['visitorAttemps', 'visitRequest:id,request_number'])->findOrFail($id);
        return Inertia::render('visitors/VisitorShow', [
            'title' => 'Visitor Details',
            'visitor' => $visitor,
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
}
