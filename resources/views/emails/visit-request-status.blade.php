<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Pengajuan Kunjungan {{ $visit->request_number }}</title>
</head>

<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial, Helvetica, sans-serif;color:#111827;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:40px 20px;">
                <table width="640" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">

                    <!-- HEADER -->
                    <tr>
                        <td style="background:#111827;padding:28px 30px;color:#ffffff;">
                            <h1 style="margin:0;font-size:22px;">LDX Data Center</h1>
                            <p style="margin:6px 0 0;font-size:13px;color:#d1d5db;">Pengajuan Kunjungan</p>
                        </td>
                    </tr>

                    <!-- STATUS -->
                    <tr>
                        <td style="padding:28px 30px 8px;">
                            @if ($approved)
                                <p style="display:inline-block;margin:0 0 14px;padding:6px 12px;border-radius:999px;background:#dcfce7;color:#166534;font-size:13px;font-weight:bold;">
                                    Disetujui
                                </p>
                                <p style="margin:0;font-size:15px;line-height:1.6;">
                                    Pengajuan kunjungan <strong>{{ $visit->request_number }}</strong> dari
                                    <strong>{{ $visit->client?->company_name }}</strong> telah disetujui.
                                    Pada hari kunjungan, tamu cukup melakukan check-in di Buku Tamu menggunakan kode di bawah ini.
                                </p>
                            @else
                                <p style="display:inline-block;margin:0 0 14px;padding:6px 12px;border-radius:999px;background:#fee2e2;color:#991b1b;font-size:13px;font-weight:bold;">
                                    Ditolak
                                </p>
                                <p style="margin:0;font-size:15px;line-height:1.6;">
                                    Mohon maaf, pengajuan kunjungan <strong>{{ $visit->request_number }}</strong> tidak dapat disetujui.
                                </p>
                                <p style="margin:14px 0 0;padding:12px 14px;border-radius:8px;background:#fef2f2;color:#991b1b;font-size:14px;">
                                    <strong>Alasan:</strong> {{ $visit->rejection_reason }}
                                </p>
                            @endif
                        </td>
                    </tr>

                    <!-- CHECK-IN CODE -->
                    @if ($approved)
                        <tr>
                            <td style="padding:16px 30px;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:10px;">
                                    <tr>
                                        <td align="center" style="padding:22px;">
                                            <p style="margin:0;font-size:12px;color:#6b7280;letter-spacing:1px;">KODE KUNJUNGAN</p>
                                            <p style="margin:8px 0 14px;font-size:32px;font-weight:bold;letter-spacing:6px;font-family:'Courier New',monospace;">
                                                {{ $visit->checkin_code }}
                                            </p>
                                            @if ($qrPng)
                                                <img src="{{ $message->embedData($qrPng, 'qr-kunjungan.png', 'image/png') }}"
                                                    width="200" height="200" alt="QR Check-in" style="display:block;margin:0 auto 12px;">
                                            @endif
                                            <a href="{{ $checkinUrl }}"
                                                style="display:inline-block;padding:10px 18px;border-radius:8px;background:#1f3fb6;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;">
                                                Buka Halaman Check-in
                                            </a>
                                            <p style="margin:12px 0 0;font-size:12px;color:#6b7280;line-height:1.5;">
                                                Check-in hanya berlaku pada tanggal kunjungan. Setiap tamu memvalidasi
                                                data dengan 4 digit terakhir NIK/Paspor.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <!-- DETAIL -->
                    <tr>
                        <td style="padding:12px 30px;">
                            <table width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;">
                                <tr>
                                    <td style="color:#6b7280;width:150px;">Tanggal</td>
                                    <td><strong>{{ $visit->visit_date->translatedFormat('l, d F Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="color:#6b7280;">Jam</td>
                                    <td>{{ substr($visit->start_time, 0, 5) }} – {{ substr($visit->end_time, 0, 5) }} WIB</td>
                                </tr>
                                <tr>
                                    <td style="color:#6b7280;">Jenis</td>
                                    <td>{{ $visit->type_label }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#6b7280;">Keperluan</td>
                                    <td>{{ $visit->purpose }}</td>
                                </tr>
                                @if ($visit->racks->isNotEmpty())
                                    <tr>
                                        <td style="color:#6b7280;">Rack</td>
                                        <td>
                                            {{ $visit->racks->map(fn ($r) => trim(($r->room?->locationDataCenter?->name ? $r->room->locationDataCenter->name . ' · ' : '') . ($r->code ?? $r->name)))->implode(', ') }}
                                        </td>
                                    </tr>
                                @endif
                                @if ($approved && $visit->admin_notes)
                                    <tr>
                                        <td style="color:#6b7280;">Catatan LDX</td>
                                        <td>{{ $visit->admin_notes }}</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <!-- MEMBERS -->
                    <tr>
                        <td style="padding:8px 30px 24px;">
                            <p style="margin:0 0 8px;font-size:14px;font-weight:bold;">Tamu ({{ $visit->members->count() }} orang)</p>
                            <table width="100%" cellpadding="8" cellspacing="0" style="font-size:13px;border-collapse:collapse;">
                                @foreach ($visit->members as $member)
                                    <tr style="border-top:1px solid #f3f4f6;">
                                        <td>{{ $loop->iteration }}. <strong>{{ $member->name }}</strong></td>
                                        <td style="color:#6b7280;">{{ $member->company_name }} · {{ $member->position_in_company }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="padding:20px 30px;background:#f9fafb;font-size:12px;color:#6b7280;line-height:1.6;">
                            Detail pengajuan dapat dilihat di portal client:
                            <a href="{{ $portalUrl }}" style="color:#1f3fb6;">{{ $portalUrl }}</a><br>
                            Email ini dikirim otomatis oleh sistem LDX Data Center.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
