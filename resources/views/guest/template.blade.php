<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu - Data Center</title>
    @vite('resources/css/app.css')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>LDX Data Center</title>
    {{-- favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/logos/ldx-box.png') }}">
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-50">
    <!-- Header -->
    <header class="bg-white border-b border-blue-100">
        <div class="max-w-6xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <button id="sidebarToggle" class="md:hidden p-2 hover:bg-gray-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor"
                                class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                            </svg>
                        </button>
                        <div class="text-xl font-bold text-gray-800">
                            <img src="{{ asset('assets/logos/ldx-logo.png') }}" alt="LDX Logo"
                                class="w-28 inline-block mr-2">
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="text-sm text-gray-600 font-bold">Reliability in Every Byte</h4>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="w-full bg-white text-black py-12 px-6 font-[Poppins]">
        <div class="max-w-7xl mx-auto">

            <!-- TOP GRID -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">

                <!-- Kolom 1 -->
                <div>
                    <h3 class="font-semibold mb-4">Paket Colocation</h3>
                    <ul class="space-y-2 text-sm list-none p-0">
                        <li>SUB RACKS / 10U</li>
                        <li>HALF RACK</li>
                        <li>FULL RACK</li>
                    </ul>
                </div>

                <!-- Kolom 2 -->
                <div>
                    <h3 class="font-semibold mb-4">Features</h3>
                    <ul class="space-y-2 text-sm list-none p-0">
                        <li>LDX Colocation</li>
                        <li>Cross-Connect</li>
                        <li>Secure</li>
                        <li>24/7 Support</li>
                        <li>Real-Time Monitoring</li>
                        <li>Uptime Guaranteed</li>
                    </ul>
                </div>

                <!-- Kolom 3 -->
                <div>
                    <h3 class="font-semibold mb-4">Ikuti Kami</h3>
                    <ul class="space-y-2 text-sm list-none p-0">
                        <li>Facebook</li>
                        <li>Instagram</li>
                        <li>Tiktok</li>
                        <li>Youtube</li>
                    </ul>
                </div>

                <!-- Kolom 4 -->
                <div>
                    <h3 class="font-semibold mb-4">Alamat</h3>
                    <p class="text-sm leading-relaxed mb-6">
                        Ruko Gardenia Blok E No. 23 Desa Rajeg Mulia, Kec. Rajeg,
                        Kab. Tangera
                    </p>
                    <p class="text-sm leading-relaxed mb-6">
                        Gedung Cyber 1- Lt. 10
                    </p>

                    <h3 class="font-semibold mb-2">Oprsional</h3>
                    <p class="text-sm leading-relaxed">
                        Senin – Jumat<br>
                        8:00 AM – 21:00 PM<br><br>

                        Sabtu – Minggu<br>
                        8:00 AM – 17:00 PM
                    </p>
                </div>

            </div>

            <!-- BORDER LINE -->
            <div class="w-full border-t mt-12 mb-6"></div>

            <!-- BOTTOM BAR -->
            <div class="flex flex-col md:flex-row justify-between text-sm">
                <p>LDX Data Center, All Rights Reserved</p>

                <div class="flex gap-6 mt-3 md:mt-0">
                    <a href="#" class="hover:underline">FAQ’s</a>
                    <a href="#" class="hover:underline">Terms & Condition</a>
                    <a href="#" class="hover:underline">Privacy Policy</a>
                </div>
            </div>

        </div>
    </footer>
</body>

</html>
