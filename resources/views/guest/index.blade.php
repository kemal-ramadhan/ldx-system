@extends('guest.template')

@php
    $inputClass = 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none';
    $errorClass = 'border-red-500 bg-red-50';
    $fieldClass = fn ($name) => $inputClass . ($errors->has($name) ? ' ' . $errorClass : '');
@endphp

@section('content')
    <!-- Main Content -->
    <main class="mx-auto px-4 py-5">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Form Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-8">
                <h2 class="text-3xl font-bold text-white mb-2">Pendaftaran Pengunjung</h2>
                <p class="text-blue-100">Silahkan isi formulir di bawah untuk melanjutkan kunjungan Anda</p>
            </div>

            <!-- Form Body -->
            <form id="visitorForm" class="p-8 space-y-6" action="{{ route('create.buku.tamu') }}" method="POST" novalidate>
                @csrf
                <!-- Personal Information Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">1</span>
                        Informasi Pribadi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" id="name" name="name" required maxlength="255"
                                data-label="Nama Lengkap" value="{{ old('name') }}"
                                placeholder="Masukkan nama lengkap" class="{{ $fieldClass('name') }}">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" id="email" name="email" required maxlength="255"
                                data-label="Email" value="{{ old('email') }}"
                                placeholder="nama@example.com" class="{{ $fieldClass('email') }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon *</label>
                            <input type="tel" id="phone" name="phone" required maxlength="20"
                                data-label="Nomor Telepon" value="{{ old('phone') }}"
                                placeholder="081234567890" class="{{ $fieldClass('phone') }}">
                        </div>
                        <div>
                            <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">Identitas (KTP/Paspor) *</label>
                            <input type="text" id="nik" name="nik" required maxlength="20"
                                data-label="Identitas (KTP/Paspor)" value="{{ old('nik') }}"
                                placeholder="Nomor KTP/Paspor" class="{{ $fieldClass('nik') }}">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat *</label>
                        <textarea id="address" name="address" rows="4" required maxlength="255" data-label="Alamat"
                            placeholder="Alamat Anda" class="{{ $fieldClass('address') }} resize-none">{{ old('address') }}</textarea>
                    </div>
                </div>

                <!-- Company Information Section -->
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">2</span>
                        Informasi Perusahaan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Perusahaan *</label>
                            <input type="text" id="company_name" name="company_name" required maxlength="255"
                                data-label="Nama Perusahaan" value="{{ old('company_name') }}"
                                placeholder="Nama perusahaan" class="{{ $fieldClass('company_name') }}">
                        </div>
                        <div>
                            <label for="position_in_company" class="block text-sm font-medium text-gray-700 mb-2">Posisi/Jabatan *</label>
                            <input type="text" id="position_in_company" name="position_in_company" required maxlength="255"
                                data-label="Posisi/Jabatan" value="{{ old('position_in_company') }}"
                                placeholder="Posisi Anda" class="{{ $fieldClass('position_in_company') }}">
                        </div>
                    </div>
                </div>

                <!-- Visit Information Section -->
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">3</span>
                        Informasi Kunjungan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="type_of_visit" class="block text-sm font-medium text-gray-700 mb-2">Tujuan Kunjungan *</label>
                            @php
                                $visitTypes = [
                                    'Kunjungan' => 'Kunjungan',
                                    'meeting' => 'Meeting',
                                    'inspeksi' => 'Inspeksi',
                                    'maintenance' => 'Pemeliharaan',
                                    'troubleshooting' => 'Troubleshooting',
                                    'deliveries' => 'Pengiriman',
                                    'lainnya' => 'Lainnya',
                                ];
                            @endphp
                            <select id="type_of_visit" name="type_of_visit" required data-label="Tujuan Kunjungan"
                                class="{{ $fieldClass('type_of_visit') }}">
                                <option value="">-- Pilih Tujuan --</option>
                                @foreach ($visitTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type_of_visit', $defaultType ?? null) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="visit_purpose" class="block text-sm font-medium text-gray-700 mb-2">Area yang Dikunjungi *</label>
                            <input type="text" id="visit_purpose" name="visit_purpose" required maxlength="255"
                                data-label="Area yang Dikunjungi" value="{{ old('visit_purpose') }}"
                                placeholder="Area yang Anda kunjungi" class="{{ $fieldClass('visit_purpose') }}">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="visit_note" class="block text-sm font-medium text-gray-700 mb-2">Keperluan Khusus / Catatan</label>
                        <textarea id="visit_note" name="visit_note" rows="4" maxlength="255"
                            data-label="Keperluan Khusus / Catatan"
                            placeholder="Tuliskan keperluan atau informasi tambahan..."
                            class="{{ $fieldClass('visit_note') }} resize-none">{{ old('visit_note') }}</textarea>
                    </div>
                </div>

                <!-- Date & Time Section -->
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">4</span>
                        Waktu Kunjungan
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="visit_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kunjungan *</label>
                            <input type="date" id="visit_date" name="visit_date" required
                                data-label="Tanggal Kunjungan" value="{{ old('visit_date') }}"
                                class="{{ $fieldClass('visit_date') }}">
                        </div>
                    </div>
                </div>

                <!-- Agreement Section -->
                <div class="pt-4 border-t border-gray-200">
                    <div id="agreementBox"
                        class="bg-blue-50 border rounded-lg p-4 {{ $errors->has('agreement') ? 'border-red-500' : 'border-blue-200' }}">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" id="agreement" name="agreement" value="1" required
                                data-label="Persetujuan Peraturan dan Kebijakan Keamanan" @checked(old('agreement'))
                                class="mt-1 w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Saya telah membaca dan menyetujui <strong>Peraturan dan
                                    Kebijakan Keamanan</strong> fasilitas Data Center, termasuk tetapi tidak terbatas
                                pada penggunaan peralatan, keamanan data, dan peraturan lainnya.</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-gray-200 flex gap-3">
                    <button type="submit" id="btn-send"
                        class="flex-1 bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Daftar Kunjungan
                    </button>
                    <button type="button" id="btn-reset"
                        class="bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg hover:bg-gray-300 transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-600 rounded-lg p-6">
            <h4 class="font-semibold text-gray-900 mb-2">Informasi Penting</h4>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>✓ Pastikan semua data yang Anda isi benar dan lengkap</li>
                <li>✓ Kartu identitas akan diminta saat check-in</li>
                <li>✓ Durasi kunjungan maksimal sesuai dengan jadwal yang sudah disetujui</li>
                <li>✓ Ikuti semua peraturan keamanan fasilitas</li>
            </ul>
        </div>
    </main>

    <!-- Popup: data yang kurang / salah -->
    <div id="errorModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        role="dialog" aria-modal="true" aria-labelledby="errorModalTitle">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
            <div class="flex items-start gap-3 border-b border-gray-100 px-6 py-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3.75m0 3.75h.008M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <h3 id="errorModalTitle" class="text-lg font-semibold text-gray-900">Data belum lengkap</h3>
                    <p id="errorModalDesc" class="mt-1 text-sm text-gray-600">
                        Mohon lengkapi data berikut. Data yang sudah Anda isi tetap tersimpan.
                    </p>
                </div>
            </div>
            <ul id="errorList" class="max-h-72 list-disc space-y-1 overflow-y-auto px-10 py-4 text-sm text-red-700"></ul>
            <div class="flex justify-end border-t border-gray-100 px-6 py-4">
                <button type="button" id="errorModalClose"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Lengkapi Data
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('visitorForm');
            const btn = document.getElementById('btn-send');
            const modal = document.getElementById('errorModal');
            const list = document.getElementById('errorList');
            const desc = document.getElementById('errorModalDesc');
            const ERROR_CLASSES = ['border-red-500', 'bg-red-50'];
            let firstInvalid = null;
            const btnHtml = btn.innerHTML;

            // Errors returned by the server (validation / session expired)
            const serverErrors = @json($errors->getMessages());

            const fields = () => Array.from(form.querySelectorAll('[data-label]'));

            const setInvalid = (el, invalid) => {
                if (el.type === 'checkbox') {
                    const box = document.getElementById('agreementBox');
                    box.classList.toggle('border-red-500', invalid);
                    box.classList.toggle('border-blue-200', !invalid);
                    return;
                }
                ERROR_CLASSES.forEach((c) => el.classList.toggle(c, invalid));
            };

            const messageFor = (el) => {
                const label = el.dataset.label;
                const v = el.validity;
                if (el.type === 'checkbox') return `${label} wajib dicentang`;
                if (v.valueMissing) return `${label} wajib diisi`;
                if (v.typeMismatch && el.type === 'email') return `Format ${label} tidak valid (contoh: nama@example.com)`;
                if (v.tooLong) return `${label} maksimal ${el.maxLength} karakter`;
                if (v.badInput) return `${label} tidak valid`;
                return `${label} tidak valid`;
            };

            const openModal = (title, messages, note) => {
                document.getElementById('errorModalTitle').textContent = title;
                desc.textContent = note;
                list.innerHTML = '';
                messages.forEach((m) => {
                    const li = document.createElement('li');
                    li.textContent = m;
                    list.appendChild(li);
                });
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.getElementById('errorModalClose').focus();
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus({ preventScroll: true });
                }
            };

            document.getElementById('errorModalClose').addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });

            // trim spaces so "   " is treated as empty
            const trimValues = () => fields().forEach((el) => {
                if (el.type !== 'checkbox' && typeof el.value === 'string') el.value = el.value.trim();
            });

            form.addEventListener('submit', (event) => {
                trimValues();

                const invalid = fields().filter((el) => !el.checkValidity());
                fields().forEach((el) => setInvalid(el, invalid.includes(el)));

                if (invalid.length) {
                    event.preventDefault();
                    firstInvalid = invalid[0];
                    openModal('Data belum lengkap', invalid.map(messageFor),
                        'Mohon lengkapi data berikut. Data yang sudah Anda isi tetap tersimpan.');
                    return;
                }

                // valid: prevent double submit
                btn.disabled = true;
                btn.innerHTML = `
                    Mengirim...
                    <svg class="animate-spin h-5 w-5 text-white inline-block ml-2"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>`;
            });

            // clear the red state as soon as the field is fixed
            fields().forEach((el) => {
                const evt = el.type === 'checkbox' || el.tagName === 'SELECT' ? 'change' : 'input';
                el.addEventListener(evt, () => { if (el.checkValidity()) setInvalid(el, false); });
            });

            // re-enable the button when coming back via the browser back button (bfcache)
            window.addEventListener('pageshow', (e) => {
                if (e.persisted) { btn.disabled = false; btn.innerHTML = btnHtml; }
            });

            // Reset asks first, so data isn't wiped by accident
            document.getElementById('btn-reset').addEventListener('click', () => {
                if (confirm('Kosongkan semua isian formulir?')) {
                    form.reset();
                    form.querySelectorAll('input:not([type=hidden]):not([type=checkbox]), textarea, select')
                        .forEach((el) => { el.value = ''; setInvalid(el, false); });
                    form.querySelector('#agreement').checked = false;
                    setInvalid(form.querySelector('#agreement'), false);
                }
            });

            // show server-side errors in the same popup
            const messages = Object.values(serverErrors).flat();
            if (messages.length) {
                const names = Object.keys(serverErrors).filter((n) => n !== 'general');
                firstInvalid = names.length ? form.querySelector(`[name="${names[0]}"]`) : null;
                openModal(
                    serverErrors.general ? 'Data belum terkirim' : 'Data belum lengkap',
                    messages,
                    'Data yang sudah Anda isi tetap tersimpan, silakan perbaiki lalu kirim ulang.'
                );
            }
        })();
    </script>
@endsection
