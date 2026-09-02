@extends('guest.template')

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
            <form id="visitorForm" class="p-8 space-y-6" action="{{ route('create.buku.tamu') }}" method="POST">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" name="name" required placeholder="Masukkan nama lengkap"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" name="email" required placeholder="nama@example.com"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon *</label>
                            <input type="tel" name="phone" required placeholder="081234567890"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Identitas (KTP/Paspor) *</label>
                            <input type="text" name="nik" required placeholder="Nomor KTP/Paspor"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea name="address" rows="4" placeholder="Alamat Anda"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none resize-none"></textarea>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Perusahaan *</label>
                            <input type="text" name="company_name" required placeholder="Nama perusahaan"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Posisi/Jabatan *</label>
                            <input type="text" name="position_in_company" required placeholder="Posisi Anda"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan Kunjungan *</label>
                            <select name="type_of_visit" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
                                <option value="">-- Pilih Tujuan --</option>
                                <option value="Kunjungan">Kunjungan</option>
                                <option value="meeting">Meeting</option>
                                <option value="inspeksi">Inspeksi</option>
                                <option value="maintenance">Pemeliharaan</option>
                                <option value="troubleshooting">Troubleshooting</option>
                                <option value="deliveries">Pengiriman</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Area yang Dikunjungi *</label>
                            <input type="text" name="visit_purpose" required placeholder="Area yang Anda kunjungi"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan Khusus / Catatan</label>
                        <textarea name="visit_note" rows="4" placeholder="Tuliskan keperluan atau informasi tambahan..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none resize-none"></textarea>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kunjungan *</label>
                            <input type="date" name="visit_date" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                </div>

                <!-- Agreement Section -->
                <div class="pt-4 border-t border-gray-200">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="agreement" required
                                class="mt-1 w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Saya telah membaca dan menyetujui <strong>Peraturan dan
                                    Kebijakan Keamanan</strong> fasilitas Data Center, termasuk tetapi tidak terbatas
                                pada penggunaan peralatan, keamanan data, dan peraturan lainnya.</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-gray-200 flex gap-3">
                    <button type="submit" id="btn-send" onclick="handleSubmit(this, event)"
                        class="flex-1 bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Daftar Kunjungan
                    </button>
                    <button type="reset"
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

    <script>
        function handleSubmit(btn, event) {
            event.preventDefault(); // cegah submit default

            btn.disabled = true; // cegah klik ganda
            btn.innerHTML = `
                    Mengirim...
                    <svg class="animate-spin h-5 w-5 text-white inline-block ml-2"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                `;

            // submit form secara normal lewat JS
            btn.closest("form").submit();
        }
    </script>
@endsection
