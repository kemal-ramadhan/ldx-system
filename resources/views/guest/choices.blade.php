@extends('guest.template', ['title' => 'Pilihan'])

@section('content')
    <div class="min-h-screen flex flex-col items-center justify-center">
        <h1 class="text-center text-3xl md:text-4xl font-bold text-gray-900 mb-20">
            Pilihan Jenis Kunjungan
        </h1>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- Card 1: Berkunjung -->
            <div class="card-hover">
                <a href="{{ route('daftar.buku.tamu', ['jenis' => 'berkunjung']) }}"
                    class="group block w-full h-full bg-white rounded-2xl p-8 shadow-md
          border-2 border-transparent
          hover:border-blue-500 hover:shadow-lg
          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
          transition-all">

                    <div class="flex flex-col items-center text-center">
                        <div
                            class="bg-gradient-to-br from-blue-100 to-blue-200
                   rounded-full p-6 mb-6
                   group-hover:from-blue-200 group-hover:to-blue-300
                   transition-all">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            Berkunjung
                        </h3>

                        <p class="text-sm text-gray-600">
                            Kunjungan ke data center untuk keperluan bisnis atau evaluasi
                        </p>
                    </div>
                </a>
            </div>

            <!-- Card 2: Perawatan -->
            <div class="card-hover">
                <a href="{{ route('daftar.buku.tamu', ['jenis' => 'perawatan']) }}"
                    class="block w-full h-full bg-white rounded-2xl p-8 shadow-md border-2 border-transparent hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 group">
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-full p-6 mb-6 group-hover:from-cyan-200 group-hover:to-cyan-300 transition-all">
                            <svg class="w-10 h-10 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Perawatan</h3>
                        <p class="text-gray-600 text-center text-sm">Perawatan dan maintenance fasilitas data center</p>
                    </div>
                </a>
            </div>

            <!-- Card 3: Lainnya -->
            <div class="card-hover">
                <a href="{{ route('daftar.buku.tamu', ['jenis' => 'lainnya']) }}"
                    class="block w-full h-full bg-white rounded-2xl p-8 shadow-md border-2 border-transparent hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 group">
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-full p-6 mb-6 group-hover:from-indigo-200 group-hover:to-indigo-300 transition-all">
                            <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Lainnya</h3>
                        <p class="text-gray-600 text-center text-sm">Kunjungan atau keperluan lainnya yang tidak masuk
                            kategori
                            di atas</p>
                    </div>
                </a>
            </div>

            <!-- Card 4: Sudah punya kode (pengajuan kunjungan dari portal client) -->
            <div class="card-hover">
                <a href="{{ route('kunjungan.checkin') }}"
                    class="group block w-full h-full rounded-2xl p-8 shadow-md border-2 border-blue-500 bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-white/20 rounded-full p-6 mb-6">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Sudah Punya Kode Kunjungan</h3>
                        <p class="text-blue-100 text-center text-sm">Kunjungan yang sudah diajukan &amp; disetujui. Cukup check-in dengan kode / QR.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    @if (request('success'))
        <div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center">

                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <h2 class="text-lg font-semibold text-gray-800 mb-2">
                    Data Tersimpan
                </h2>

                <p class="text-gray-600 mb-6">
                    Data buku tamu berhasil disimpan
                </p>

                <button onclick="closeSuccessModal()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">
                    OK
                </button>

            </div>
        </div>

        <script>
            function closeSuccessModal() {
                document.getElementById('successModal').remove()
            }

            setTimeout(() => {
                const modal = document.getElementById('successModal')
                if (modal) modal.remove()
            }, 3000)
        </script>
    @endif
@endsection
