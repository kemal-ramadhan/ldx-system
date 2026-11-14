@extends('guest.template')

{{--
    Self check-in for an approved "pengajuan kunjungan".
    Steps: code -> guests (pick name + last 4 NIK digits) -> confirm -> photo
--}}

@section('content')
    <main class="mx-auto max-w-3xl px-4 py-5">
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-7">
                <h2 class="mb-1 text-2xl font-bold text-white">Check-in Kunjungan</h2>
                <p class="text-blue-100">Untuk tamu yang kunjungannya sudah diajukan & disetujui melalui portal client.</p>

                {{-- stepper --}}
                @php
                    $steps = ['code' => 'Kode', 'guests' => 'Pilih Nama', 'confirm' => 'Konfirmasi'];
                    $index = array_search($step, array_keys($steps));
                @endphp
                <ol class="mt-5 flex items-center gap-2 text-xs font-semibold">
                    @foreach ($steps as $key => $label)
                        <li class="flex items-center gap-2 {{ $loop->index <= $index ? 'text-white' : 'text-blue-200' }}">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $loop->index <= $index ? 'bg-white text-blue-700' : 'bg-blue-500 text-blue-100' }}">
                                {{ $loop->iteration }}
                            </span>
                            {{ $label }}
                            @unless ($loop->last)
                                <span class="mx-1 h-px w-6 bg-blue-300"></span>
                            @endunless
                        </li>
                    @endforeach
                    <li class="flex items-center gap-2 text-blue-200">
                        <span class="mx-1 h-px w-6 bg-blue-300"></span>
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-500 text-blue-100">4</span>
                        Foto
                    </li>
                </ol>
            </div>

            <div class="p-8">
                @if (session('error'))
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
                @endif

                {{-- ===================== STEP 1: CODE ===================== --}}
                @if ($step === 'code')
                    <form method="POST" action="{{ route('kunjungan.checkin.verify') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label for="code" class="mb-2 block text-sm font-medium text-gray-700">Kode Kunjungan</label>
                            <input id="code" name="code" value="{{ old('code', $code) }}" maxlength="12" autocomplete="off" autofocus
                                placeholder="Contoh: AB7KQ2MZ"
                                class="w-full rounded-lg border px-4 py-4 text-center font-mono text-2xl uppercase tracking-[0.4em] outline-none transition focus:border-transparent focus:ring-2 focus:ring-blue-500 {{ $errors->has('code') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                            @error('code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                Kode ada di email persetujuan pengajuan kunjungan atau di portal client. Bisa juga scan QR pada email.
                            </p>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700">
                            Lanjut
                        </button>

                        <p class="text-center text-sm text-gray-500">
                            Belum punya pengajuan? <a href="{{ route('daftar.buku.tamu') }}" class="font-medium text-blue-600 hover:underline">Isi Buku Tamu</a>
                        </p>
                    </form>
                @endif

                {{-- ===================== STEP 2: GUESTS ===================== --}}
                @if ($step === 'guests')
                    <div class="mb-6 rounded-xl bg-blue-50 p-4 text-sm text-gray-700">
                        <p class="font-semibold text-gray-900">{{ $visit->request_number }} · {{ $visit->client?->company_name }}</p>
                        <p class="mt-1">
                            {{ $visit->visit_date->translatedFormat('l, d F Y') }},
                            {{ substr($visit->start_time, 0, 5) }}–{{ substr($visit->end_time, 0, 5) }} WIB · {{ $visit->purpose }}
                        </p>
                    </div>

                    <p class="mb-3 text-sm text-gray-600">
                        Pilih nama Anda, lalu masukkan <strong>4 digit terakhir NIK/Paspor</strong> untuk validasi.
                    </p>

                    <ul class="space-y-3">
                        @foreach ($visit->members as $member)
                            @php $bag = 'member_' . $member->id; @endphp
                            <li class="rounded-xl border {{ $errors->{$bag}->any() ? 'border-red-300' : 'border-gray-200' }} p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $member->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $member->company_name }} · NIK {{ $member->masked_nik }}</p>
                                    </div>

                                    @if ($member->checked_in_at)
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            Sudah check-in {{ $member->checked_in_at->format('H:i') }}
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('kunjungan.checkin.identify', $member) }}" class="flex items-center gap-2">
                                            @csrf
                                            <input name="nik_last4" inputmode="numeric" maxlength="4" placeholder="4 digit"
                                                class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-center font-mono tracking-widest outline-none focus:ring-2 focus:ring-blue-500">
                                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                                Ini Saya
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                @if ($errors->{$bag}->any())
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->{$bag}->first() }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-6 text-center text-sm text-gray-500">
                        Nama tidak ada? Hubungi petugas di meja depan.
                        · <a href="{{ route('kunjungan.checkin') }}" class="font-medium text-blue-600 hover:underline">Ganti kode</a>
                    </p>
                @endif

                {{-- ===================== STEP 3: CONFIRM ===================== --}}
                @if ($step === 'confirm')
                    <p class="mb-4 text-sm text-gray-600">Periksa data Anda. Jika ada yang salah, hubungi petugas di meja depan.</p>

                    <dl class="grid grid-cols-1 gap-4 rounded-xl border border-gray-200 p-5 text-sm md:grid-cols-2">
                        @foreach ([
                            'Nama Lengkap' => $member->name,
                            'NIK / Paspor' => $member->nik,
                            'Email' => $member->email,
                            'Telepon' => $member->phone,
                            'Perusahaan' => $member->company_name,
                            'Jabatan' => $member->position_in_company,
                            'Alamat' => $member->address,
                        ] as $label => $value)
                            <div class="{{ $label === 'Alamat' ? 'md:col-span-2' : '' }}">
                                <dt class="text-xs text-gray-500">{{ $label }}</dt>
                                <dd class="mt-1 font-medium text-gray-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="mt-4 rounded-xl bg-gray-50 p-4 text-sm text-gray-700">
                        <p><strong>Kunjungan:</strong> {{ $visit->request_number }} · {{ $visit->type_label }} · {{ $visit->purpose }}</p>
                        @if ($visit->racks->isNotEmpty())
                            <p class="mt-1"><strong>Rack:</strong>
                                {{ $visit->racks->map(fn ($r) => ($r->room?->name ? $r->room->name . ' · ' : '') . ($r->code ?? $r->name))->implode(', ') }}
                            </p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('kunjungan.checkin.store') }}" class="mt-6 space-y-5">
                        @csrf
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border {{ $errors->has('agreement') ? 'border-red-500' : 'border-blue-200' }} bg-blue-50 p-4">
                            <input type="checkbox" name="agreement" value="1" class="mt-1 h-5 w-5 rounded text-blue-600">
                            <span class="text-sm text-gray-700">
                                Data di atas benar dan saya menyetujui <strong>Peraturan dan Kebijakan Keamanan</strong> fasilitas Data Center.
                            </span>
                        </label>
                        @error('agreement')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('checkin')
                            <p class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
                        @enderror

                        <div class="flex gap-3">
                            <a href="{{ route('kunjungan.checkin.guests') }}"
                                class="rounded-lg bg-gray-200 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-300">Kembali</a>
                            <button type="submit" class="flex-1 rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700">
                                Check-in Sekarang
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </main>
@endsection
