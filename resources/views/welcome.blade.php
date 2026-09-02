<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LDXDC – Colocation Server & Data Center Indonesia</title>
    {{-- favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/logos/ldx-box.png') }}">

    <meta name="description"
        content="Layanan colocation server, rack server, dan data center terpercaya di Indonesia. Uptime 99.99%, monitoring 24/7, keamanan berlapis. Mulai dari Rp 750.000/bulan." />
    <meta name="keywords"
        content="data center indonesia, colocation server, rack server murah, hosting server, colocation jakarta, sewa rack server" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Syne', 'sans-serif'],
                        body: ['DM Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        violet: {
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                        },
                        dark: {
                            900: '#03050f',
                            800: '#060d1f',
                            700: '#0b1530',
                            600: '#101d40',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-slow': 'float 9s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-up': 'slideUp 0.6s ease forwards',
                        'fade-in': 'fadeIn 0.8s ease forwards',
                        'spin-slow': 'spin 20s linear infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-16px)'
                            },
                        },
                        slideUp: {
                            from: {
                                opacity: '0',
                                transform: 'translateY(30px)'
                            },
                            to: {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        },
                        fadeIn: {
                            from: {
                                opacity: '0'
                            },
                            to: {
                                opacity: '1'
                            },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #03050f;
            color: #e2e8f0;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Syne', sans-serif;
        }

        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(96, 165, 250, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(37, 99, 235, 0.15);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 50%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 200% auto;
            animation: shimmer 4s linear infinite;
        }

        @keyframes shimmer {
            to {
                background-position: 200% center;
            }
        }

        /* Noise texture overlay */
        .noise::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* Grid pattern */
        .grid-pattern {
            background-image: linear-gradient(rgba(37, 99, 235, 0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(37, 99, 235, 0.08) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* Glow effects */
        .glow-blue {
            box-shadow: 0 0 40px rgba(37, 99, 235, 0.3), 0 0 80px rgba(37, 99, 235, 0.1);
        }

        .glow-violet {
            box-shadow: 0 0 40px rgba(124, 58, 237, 0.3), 0 0 80px rgba(124, 58, 237, 0.1);
        }

        .text-glow {
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.4);
        }

        /* Pricing badge */
        .popular-badge {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            animation: pulse-slow 3s ease-in-out infinite;
        }

        /* FAQ accordion */
        .faq-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.3s ease;
        }

        .faq-content.open {
            max-height: 300px;
        }

        .faq-icon {
            transition: transform 0.3s ease;
        }

        .faq-item.open .faq-icon {
            transform: rotate(45deg);
        }

        /* Scroll animations */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Mobile menu */
        .mobile-menu {
            transform: translateY(-110%);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .mobile-menu.open {
            transform: translateY(0);
        }

        /* Orbit ring */
        .orbit {
            border: 1px solid rgba(37, 99, 235, 0.15);
            border-radius: 50%;
            animation: spin-slow 20s linear infinite;
        }

        .orbit-2 {
            animation-direction: reverse;
            animation-duration: 30s;
        }

        /* Button styles */
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #1d4ed8, #6d28d9);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-primary:hover::before {
            opacity: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(37, 99, 235, 0.4);
        }

        /* Server rack SVG animation */
        .rack-light {
            animation: blink 2s ease-in-out infinite;
        }

        .rack-light-2 {
            animation: blink 2.5s ease-in-out infinite 0.5s;
        }

        .rack-light-3 {
            animation: blink 1.8s ease-in-out infinite 1s;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        /* Navbar scroll effect */
        #navbar {
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        #navbar.scrolled {
            background: rgba(3, 5, 15, 0.85);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        /* Hamburger */
        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: #e2e8f0;
            transition: all 0.3s ease;
            margin: 5px 0;
        }

        .hamburger.open span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .hamburger.open span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.open span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: #03050f;
        }

        ::-webkit-scrollbar-thumb {
            background: #2563eb;
            border-radius: 4px;
        }

        .mobile-menu {
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .mobile-menu.open {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
    </style>
</head>

<body class="antialiased">

    <!-- ═══════════════════════════ NAVBAR ═══════════════════════════ -->
    <header id="navbar" class="fixed top-0 left-0 right-0 z-50 glass">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-18">
                <!-- Logo -->
                <a href="#" class="flex items-center gap-2.5 group" aria-label="LDXDC Home">
                    <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center relative z-10">
                        <img src="{{ asset('assets/logos/ldx-box.png') }}" alt="logo">
                    </div>
                    <span class="font-display font-800 text-white text-xl tracking-tight">LDX<span
                            class="gradient-text">Data Center</span></span>
                </a>

                <!-- Desktop nav -->
                <nav class="hidden lg:flex items-center gap-8" aria-label="Main navigation">
                    <a href="#features"
                        class="text-slate-400 hover:text-white text-sm font-medium transition-colors duration-200">Features</a>
                    <a href="#paket"
                        class="text-slate-400 hover:text-white text-sm font-medium transition-colors duration-200">Paket</a>
                    <a href="#infrastruktur"
                        class="text-slate-400 hover:text-white text-sm font-medium transition-colors duration-200">Infrastruktur</a>
                    <a href="#faq"
                        class="text-slate-400 hover:text-white text-sm font-medium transition-colors duration-200">FAQ</a>
                    <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                        class="text-slate-400 hover:text-white text-sm font-medium transition-colors duration-200">Kontak</a>
                </nav>

                <!-- CTA Buttons -->
                <div class="hidden lg:flex items-center gap-3">
                    <a href="/login"
                        class="text-slate-300 hover:text-white text-sm font-medium px-4 py-2 rounded-lg border border-white/10 hover:border-white/20 transition-all duration-200">Login</a>
                    <a href="/register"
                        class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-lg relative z-10">Register</a>
                </div>

                <!-- Mobile hamburger -->
                <button class="hamburger lg:hidden p-1" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="mobile-menu lg:hidden absolute top-full left-0 right-0 bg-slate-900/90 border-t border-white/06 py-4 px-4"
            id="mobileMenu">
            <nav class="flex flex-col gap-1" aria-label="Mobile navigation">
                <a href="#features"
                    class="mobile-link text-slate-300 hover:text-white px-4 py-3 rounded-lg hover:bg-white/05 transition-all text-sm font-medium">Features</a>
                <a href="#paket"
                    class="mobile-link text-slate-300 hover:text-white px-4 py-3 rounded-lg hover:bg-white/05 transition-all text-sm font-medium">Paket</a>
                <a href="#infrastruktur"
                    class="mobile-link text-slate-300 hover:text-white px-4 py-3 rounded-lg hover:bg-white/05 transition-all text-sm font-medium">Infrastruktur</a>
                <a href="#faq"
                    class="mobile-link text-slate-300 hover:text-white px-4 py-3 rounded-lg hover:bg-white/05 transition-all text-sm font-medium">FAQ</a>
            </nav>
        </div>
    </header>

    <!-- ═══════════════════════════ HERO ═══════════════════════════ -->
    <section id="home" class="relative min-h-screen flex items-center pt-16 overflow-hidden grid-pattern noise">
        <!-- Background blobs -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-violet-600/15 rounded-full blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-900/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 w-full relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Text -->
                <div>
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-blue-500/30 bg-blue-500/10 text-blue-400 text-xs font-medium mb-6 reveal">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        Uptime 99.99% — Tersertifikasi Tier III
                    </div>

                    <h1 class="font-display font-800 text-5xl sm:text-6xl lg:text-7xl leading-[1.05] text-white mb-6 reveal"
                        style="transition-delay:0.1s">
                        Reliability<br />
                        <span class="gradient-text text-glow">in Every</span><br />
                        Byte
                    </h1>

                    <p class="text-slate-400 text-lg lg:text-xl leading-relaxed max-w-lg mb-10 reveal"
                        style="transition-delay:0.2s">
                        Infrastruktur data center enterprise-grade dengan colocation server yang aman, handal, dan
                        terjangkau. Solusi terpercaya untuk bisnis Anda di Indonesia.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 reveal" style="transition-delay:0.3s">
                        <a href="#paket"
                            class="btn-primary text-white font-semibold px-7 py-3.5 rounded-xl text-base flex items-center justify-center gap-2 relative z-10">
                            Mulai Sekarang
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                        <a href="#features"
                            class="text-white font-semibold px-7 py-3.5 rounded-xl text-base border border-white/15 hover:border-white/30 hover:bg-white/05 transition-all duration-300 flex items-center justify-center gap-2">
                            Lihat Layanan
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8 3v10M3 9l5 4 5-4" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 mt-12 pt-10 border-t border-white/06 reveal"
                        style="transition-delay:0.4s">
                        <div>
                            <p class="font-display text-3xl font-700 text-white">99.99<span
                                    class="text-blue-400 text-xl">%</span></p>
                            <p class="text-slate-500 text-sm mt-0.5">Uptime SLA</p>
                        </div>
                        <div>
                            <p class="font-display text-3xl font-700 text-white">24<span
                                    class="text-blue-400 text-xl">/7</span></p>
                            <p class="text-slate-500 text-sm mt-0.5">NOC Support</p>
                        </div>
                        <div>
                            <p class="font-display text-3xl font-700 text-white">500<span
                                    class="text-blue-400 text-xl">+</span></p>
                            <p class="text-slate-500 text-sm mt-0.5">Klien Aktif</p>
                        </div>
                    </div>
                </div>

                <!-- 3D Server Rack Illustration -->
                <div class="flex justify-center items-center relative reveal" style="transition-delay:0.2s">
                    <!-- Orbit rings -->
                    <div class="absolute w-80 h-80 orbit opacity-30"></div>
                    <div class="absolute w-[420px] h-[420px] orbit orbit-2 opacity-20"></div>

                    <!-- Floating dots -->
                    <div class="absolute top-8 right-16 w-3 h-3 bg-blue-400 rounded-full animate-float"
                        style="animation-delay:0s"></div>
                    <div class="absolute top-1/3 left-8 w-2 h-2 bg-violet-400 rounded-full animate-float"
                        style="animation-delay:1s"></div>
                    <div class="absolute bottom-12 right-12 w-2 h-2 bg-green-400 rounded-full animate-float"
                        style="animation-delay:2s"></div>

                    <!-- Server rack SVG illustration -->
                    <div class="animate-float relative">
                        <div class="absolute inset-0 bg-blue-500/20 rounded-3xl blur-3xl scale-110"></div>
                        <svg viewBox="0 0 340 480" width="340" height="480" xmlns="http://www.w3.org/2000/svg"
                            class="relative drop-shadow-2xl" aria-label="Server rack data center illustration"
                            role="img">
                            <defs>
                                <linearGradient id="rackGrad" x1="0" y1="0" x2="1"
                                    y2="1">
                                    <stop offset="0%" stop-color="#1e3a8a" />
                                    <stop offset="100%" stop-color="#0f172a" />
                                </linearGradient>
                                <linearGradient id="panelGrad" x1="0" y1="0" x2="0"
                                    y2="1">
                                    <stop offset="0%" stop-color="#1e40af" stop-opacity="0.9" />
                                    <stop offset="100%" stop-color="#0f172a" stop-opacity="0.9" />
                                </linearGradient>
                                <linearGradient id="topGrad" x1="0" y1="0" x2="1"
                                    y2="0">
                                    <stop offset="0%" stop-color="#2563eb" stop-opacity="0.6" />
                                    <stop offset="100%" stop-color="#1d4ed8" stop-opacity="0.3" />
                                </linearGradient>
                                <linearGradient id="sideGrad" x1="0" y1="0" x2="1"
                                    y2="0">
                                    <stop offset="0%" stop-color="#1e3a8a" />
                                    <stop offset="100%" stop-color="#1e40af" />
                                </linearGradient>
                                <filter id="glow">
                                    <feGaussianBlur stdDeviation="2" result="blur" />
                                    <feMerge>
                                        <feMergeNode in="blur" />
                                        <feMergeNode in="SourceGraphic" />
                                    </feMerge>
                                </filter>
                            </defs>

                            <!-- Cabinet base 3D -->
                            <!-- Left side face -->
                            <polygon points="30,60 30,440 80,480 80,100" fill="url(#sideGrad)" opacity="0.7" />
                            <!-- Top face -->
                            <polygon points="30,60 80,100 310,100 260,60" fill="url(#topGrad)" opacity="0.9" />
                            <!-- Front face (main panel) -->
                            <rect x="80" y="100" width="230" height="380" rx="6" fill="url(#rackGrad)"
                                stroke="#2563eb" stroke-width="0.5" stroke-opacity="0.4" />

                            <!-- Front panel background -->
                            <rect x="92" y="112" width="206" height="356" rx="4" fill="#060d1f"
                                opacity="0.8" />

                            <!-- 1U Row 1 - Active server -->
                            <rect x="96" y="118" width="198" height="34" rx="3" fill="#0a1628"
                                stroke="#1e40af" stroke-width="0.5" />
                            <rect x="102" y="126" width="140" height="6" rx="2" fill="#1e3a8a"
                                opacity="0.6" />
                            <rect x="102" y="135" width="80" height="3" rx="1" fill="#1e3a8a"
                                opacity="0.4" />
                            <circle cx="268" cy="130" r="4" fill="#22c55e" class="rack-light"
                                filter="url(#glow)" />
                            <circle cx="256" cy="130" r="4" fill="#22c55e" class="rack-light-2"
                                filter="url(#glow)" />
                            <rect x="240" y="126" width="10" height="8" rx="1" fill="#1d4ed8"
                                opacity="0.7" />

                            <!-- 1U Row 2 -->
                            <rect x="96" y="156" width="198" height="34" rx="3" fill="#0a1628"
                                stroke="#1e40af" stroke-width="0.5" />
                            <rect x="102" y="164" width="120" height="6" rx="2" fill="#1e3a8a"
                                opacity="0.6" />
                            <rect x="102" y="173" width="60" height="3" rx="1" fill="#1e3a8a"
                                opacity="0.4" />
                            <circle cx="268" cy="168" r="4" fill="#22c55e" class="rack-light-3"
                                filter="url(#glow)" />
                            <circle cx="256" cy="168" r="4" fill="#facc15" class="rack-light"
                                filter="url(#glow)" />

                            <!-- 2U Switch / Network -->
                            <rect x="96" y="194" width="198" height="50" rx="3" fill="#050e1c"
                                stroke="#2563eb" stroke-width="0.8" />
                            <rect x="102" y="202" width="185" height="34" rx="2" fill="#0b1a35" />
                            <!-- Ports -->
                            <g fill="#1d4ed8">
                                <rect x="107" y="207" width="8" height="10" rx="1" />
                                <rect x="119" y="207" width="8" height="10" rx="1" />
                                <rect x="131" y="207" width="8" height="10" rx="1" />
                                <rect x="143" y="207" width="8" height="10" rx="1" />
                                <rect x="155" y="207" width="8" height="10" rx="1" />
                                <rect x="167" y="207" width="8" height="10" rx="1" />
                                <rect x="179" y="207" width="8" height="10" rx="1" />
                                <rect x="191" y="207" width="8" height="10" rx="1" />
                            </g>
                            <g fill="#2563eb" opacity="0.5">
                                <rect x="107" y="221" width="8" height="10" rx="1" />
                                <rect x="119" y="221" width="8" height="10" rx="1" />
                                <rect x="131" y="221" width="8" height="10" rx="1" />
                                <rect x="143" y="221" width="8" height="10" rx="1" />
                                <rect x="155" y="221" width="8" height="10" rx="1" />
                                <rect x="167" y="221" width="8" height="10" rx="1" />
                            </g>
                            <circle cx="268" cy="209" r="4" fill="#22c55e" class="rack-light-2"
                                filter="url(#glow)" />
                            <text x="218" y="228" font-size="8" fill="#3b82f6" font-family="monospace"
                                opacity="0.8">SWITCH</text>

                            <!-- 1U Row 4 -->
                            <rect x="96" y="248" width="198" height="34" rx="3" fill="#0a1628"
                                stroke="#1e40af" stroke-width="0.5" />
                            <rect x="102" y="256" width="160" height="6" rx="2" fill="#1e3a8a"
                                opacity="0.6" />
                            <circle cx="268" cy="260" r="4" fill="#22c55e" class="rack-light"
                                filter="url(#glow)" />

                            <!-- 2U Storage server -->
                            <rect x="96" y="286" width="198" height="50" rx="3" fill="#07111f"
                                stroke="#1d4ed8" stroke-width="0.5" />
                            <rect x="102" y="294" width="3" height="30" rx="1" fill="#1e40af"
                                opacity="0.6" />
                            <rect x="108" y="294" width="3" height="30" rx="1" fill="#1e40af"
                                opacity="0.6" />
                            <rect x="114" y="294" width="3" height="30" rx="1" fill="#1e40af"
                                opacity="0.4" />
                            <rect x="120" y="294" width="3" height="30" rx="1" fill="#1e40af"
                                opacity="0.6" />
                            <rect x="126" y="294" width="3" height="30" rx="1" fill="#1e40af"
                                opacity="0.3" />
                            <text x="138" y="312" font-size="7" fill="#60a5fa" font-family="monospace">STORAGE
                                ARRAY</text>
                            <text x="138" y="323" font-size="6" fill="#475569" font-family="monospace">32TB /
                                RAID-10</text>
                            <circle cx="268" cy="302" r="4" fill="#22c55e" class="rack-light-3"
                                filter="url(#glow)" />

                            <!-- 1U Row 6 -->
                            <rect x="96" y="340" width="198" height="34" rx="3" fill="#0a1628"
                                stroke="#1e40af" stroke-width="0.5" />
                            <rect x="102" y="348" width="100" height="6" rx="2" fill="#1e3a8a"
                                opacity="0.6" />
                            <circle cx="268" cy="352" r="4" fill="#facc15" class="rack-light-2"
                                filter="url(#glow)" />
                            <circle cx="256" cy="352" r="4" fill="#22c55e" class="rack-light"
                                filter="url(#glow)" />

                            <!-- PDU / Power -->
                            <rect x="96" y="378" width="198" height="28" rx="3" fill="#04080f"
                                stroke="#374151" stroke-width="0.5" />
                            <text x="107" y="396" font-size="7" fill="#6b7280" font-family="monospace">PDU — POWER
                                DISTRIBUTION</text>
                            <circle cx="268" cy="392" r="3" fill="#22c55e" class="rack-light-3" />

                            <!-- 1U Row 8 -->
                            <rect x="96" y="410" width="198" height="34" rx="3" fill="#0a1628"
                                stroke="#1e40af" stroke-width="0.5" />
                            <rect x="102" y="418" width="80" height="6" rx="2" fill="#1e3a8a"
                                opacity="0.6" />
                            <circle cx="268" cy="422" r="4" fill="#22c55e" class="rack-light"
                                filter="url(#glow)" />

                            <!-- Bottom rail -->
                            <rect x="80" y="472" width="230" height="8" rx="2" fill="#1e3a8a"
                                opacity="0.4" />

                            <!-- Cable effect left side -->
                            <path d="M80 200 Q60 250 80 300" stroke="#2563eb" stroke-width="1.5" fill="none"
                                opacity="0.3" stroke-dasharray="4,4" />
                            <path d="M80 220 Q55 270 80 320" stroke="#7c3aed" stroke-width="1" fill="none"
                                opacity="0.2" stroke-dasharray="3,5" />
                        </svg>
                    </div>

                    <!-- Floating stats cards -->
                    <div class="absolute -left-6 top-1/4 glass-card rounded-xl p-3 hidden lg:block animate-float"
                        style="animation-delay:1.5s">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M2 7l3.5 3.5L12 3.5" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-white text-xs font-semibold">All Systems</p>
                                <p class="text-green-400 text-xs">Operational</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute -right-4 bottom-1/4 glass-card rounded-xl p-3 hidden lg:block animate-float-slow">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M7 2v10M2 7h10" stroke="#60a5fa" stroke-width="1.5"
                                        stroke-linecap="round" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-white text-xs font-semibold">Bandwidth</p>
                                <p class="text-blue-400 text-xs">10 Gbps</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom fade -->
        <div
            class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-dark-900 to-transparent pointer-events-none">
        </div>
    </section>

    <!-- ═══════════════════════════ FEATURES ═══════════════════════════ -->
    <section id="features" class="py-24 lg:py-32 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-blue-400 text-sm font-semibold tracking-widest uppercase mb-3 block">Layanan
                    Kami</span>
                <h2 class="font-display text-4xl lg:text-5xl font-800 text-white mb-4">
                    Semua yang Anda <span class="gradient-text">Butuhkan</span>
                </h2>
                <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                    Infrastruktur kelas enterprise dengan teknologi terkini untuk mendukung pertumbuhan bisnis digital
                    Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature cards -->
                <!-- Colocation -->
                <article class="glass-card rounded-2xl p-6 reveal group" style="transition-delay:0.05s">
                    <div
                        class="w-12 h-12 rounded-xl bg-blue-500/15 border border-blue-500/20 flex items-center justify-center mb-5 group-hover:bg-blue-500/25 transition-colors">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="4" rx="1" stroke="#60a5fa"
                                stroke-width="1.5" />
                            <rect x="3" y="11" width="18" height="4" rx="1" stroke="#60a5fa"
                                stroke-width="1.5" />
                            <rect x="3" y="17" width="18" height="4" rx="1" stroke="#60a5fa"
                                stroke-width="1.5" />
                            <circle cx="19" cy="7" r="1" fill="#22c55e" />
                            <circle cx="19" cy="13" r="1" fill="#22c55e" />
                            <circle cx="19" cy="19" r="1" fill="#facc15" />
                        </svg>
                    </div>
                    <h3 class="font-display text-lg font-600 text-white mb-2">Colocation Server</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Tempatkan server Anda di fasilitas data center
                        kami yang aman dengan koneksi internet berkecepatan tinggi dan uptime terjamin.</p>
                </article>

                <!-- Cross Connect -->
                <article class="glass-card rounded-2xl p-6 reveal group" style="transition-delay:0.1s">
                    <div
                        class="w-12 h-12 rounded-xl bg-violet-500/15 border border-violet-500/20 flex items-center justify-center mb-5 group-hover:bg-violet-500/25 transition-colors">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 6h8M8 12h8M8 18h8" stroke="#a78bfa" stroke-width="1.5"
                                stroke-linecap="round" />
                            <path d="M4 6h2M4 12h2M4 18h2" stroke="#a78bfa" stroke-width="1.5"
                                stroke-linecap="round" />
                            <path d="M18 6h2M18 12h2M18 18h2" stroke="#a78bfa" stroke-width="1.5"
                                stroke-linecap="round" />
                            <circle cx="12" cy="12" r="2" stroke="#a78bfa" stroke-width="1.5" />
                        </svg>
                    </div>
                    <h3 class="font-display text-lg font-600 text-white mb-2">Cross Connect</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Hubungkan server Anda langsung dengan provider
                        internet, cloud, atau tenant lain dalam fasilitas kami dengan latensi ultra-rendah.</p>
                </article>

                <!-- Secure -->
                <article class="glass-card rounded-2xl p-6 reveal group" style="transition-delay:0.15s">
                    <div
                        class="w-12 h-12 rounded-xl bg-green-500/15 border border-green-500/20 flex items-center justify-center mb-5 group-hover:bg-green-500/25 transition-colors">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3L4 7v5c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V7L12 3z" stroke="#4ade80"
                                stroke-width="1.5" stroke-linejoin="round" />
                            <path d="M9 12l2 2 4-4" stroke="#4ade80" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="font-display text-lg font-600 text-white mb-2">Multi-Layer Security</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Keamanan berlapis dengan CCTV 24 jam, akses
                        biometrik, security guard, dan sistem enkripsi data end-to-end.</p>
                </article>

                <!-- 24/7 Support -->
                <article class="glass-card rounded-2xl p-6 reveal group" style="transition-delay:0.2s">
                    <div
                        class="w-12 h-12 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center mb-5 group-hover:bg-amber-500/25 transition-colors">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 7H4C3 7 2 8 2 9v10c0 1 1 2 2 2h16c1 0 2-1 2-2V9c0-1-1-2-2-2z" stroke="#fbbf24"
                                stroke-width="1.5" />
                            <path d="M16 7V5c0-1.1-.9-2-2-2h-4C8.9 3 8 3.9 8 5v2" stroke="#fbbf24"
                                stroke-width="1.5" />
                            <circle cx="12" cy="14" r="2" stroke="#fbbf24" stroke-width="1.5" />
                            <path d="M9 21v-1c0-1.7 1.3-3 3-3s3 1.3 3 3v1" stroke="#fbbf24" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3 class="font-display text-lg font-600 text-white mb-2">Support 24/7</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Tim NOC profesional siap membantu 24 jam sehari,
                        7 hari seminggu. Respons cepat dan penanganan insiden dalam hitungan menit.</p>
                </article>

                <!-- Real-Time Monitoring -->
                <article class="glass-card rounded-2xl p-6 reveal group" style="transition-delay:0.25s">
                    <div
                        class="w-12 h-12 rounded-xl bg-cyan-500/15 border border-cyan-500/20 flex items-center justify-center mb-5 group-hover:bg-cyan-500/25 transition-colors">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="14" rx="2" stroke="#22d3ee"
                                stroke-width="1.5" />
                            <path d="M7 12l2-3 3 4 2-2 2 1" stroke="#22d3ee" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7 20h10" stroke="#22d3ee" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3 class="font-display text-lg font-600 text-white mb-2">Real-Time Monitoring</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Dashboard monitoring real-time untuk memantau
                        performa server, bandwidth, suhu, dan power usage dari mana saja.</p>
                </article>

                <!-- Uptime Guarantee -->
                <article class="glass-card rounded-2xl p-6 reveal group" style="transition-delay:0.3s">
                    <div
                        class="w-12 h-12 rounded-xl bg-rose-500/15 border border-rose-500/20 flex items-center justify-center mb-5 group-hover:bg-rose-500/25 transition-colors">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path
                                d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"
                                stroke="#fb7185" stroke-width="1.5" stroke-linecap="round" />
                            <circle cx="12" cy="12" r="4" stroke="#fb7185" stroke-width="1.5" />
                        </svg>
                    </div>
                    <h3 class="font-display text-lg font-600 text-white mb-2">Uptime Guarantee</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Garansi uptime 99.99% dengan SLA yang
                        terdokumentasi. Kompensasi otomatis jika downtime melebihi batas yang disepakati.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════ PAKET COLOCATION ═══════════════════════════ -->
    <section id="paket" class="py-24 lg:py-32 relative">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-950/10 to-violet-950/5 pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <span class="text-blue-400 text-sm font-semibold tracking-widest uppercase mb-3 block">Pilih
                    Paket</span>
                <h2 class="font-display text-4xl lg:text-5xl font-800 text-white mb-4">
                    Paket <span class="gradient-text">Colocation</span>
                </h2>
                <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                    Pilih paket sesuai kebutuhan server Anda. Semua paket sudah termasuk monitoring, keamanan, dan
                    support 24/7.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    <!-- Half Rack — Popular -->
                    <article class="rounded-2xl p-7 reveal relative"
                        style="transition-delay:0.1s; background: linear-gradient(135deg, rgba(37,99,235,0.15) 0%, rgba(124,58,237,0.12) 100%); border: 1px solid rgba(37,99,235,0.4);">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                            <span
                                class="popular-badge text-white text-xs font-700 font-display px-4 py-1.5 rounded-full whitespace-nowrap">⚡
                                Paling Populer</span>
                        </div>
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-lg bg-blue-500/25 flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <rect x="3" y="4" width="14" height="12" rx="1.5" stroke="#60a5fa"
                                        stroke-width="1.5" />
                                    <rect x="5" y="7" width="10" height="2" rx="1" fill="#60a5fa"
                                        opacity="0.5" />
                                    <rect x="5" y="11" width="10" height="2" rx="1" fill="#60a5fa"
                                        opacity="0.3" />
                                    <circle cx="15" cy="8" r="1" fill="#60a5fa" />
                                    <circle cx="15" cy="12" r="1" fill="#60a5fa" />
                                </svg>
                            </div>
                            <h3 class="font-display text-lg font-700 text-white">Limited Offer - Colocation 1U</h3>
                        </div>
                        <div class="mb-6">
                            <span class="font-display text-4xl font-800 text-white">Rp 1.000.000 </span>
                            <span class="text-slate-400 text-sm">/bulan</span>
                        </div>
                        <ul class="space-y-3 mb-7">
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                1 Pair FO APJII Cyber 1st FL
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Power 200 VA
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Power Dual Sources
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Powe SUpport AC/DC
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                No-Rated Data Center
                            </li>
                        </ul>
                        <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                            class="btn-primary w-full block text-center text-white font-semibold px-5 py-3 rounded-xl relative z-10 text-sm">
                            Order Sekarang
                        </a>
                    </article>

                    <!-- Special Offer - Colocation 2U -->
                    <article class="glass-card rounded-2xl p-7 reveal" style="transition-delay:0.15s">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-lg bg-violet-500/15 flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <rect x="3" y="2" width="14" height="16" rx="1.5" stroke="#a78bfa"
                                        stroke-width="1.5" />
                                    <rect x="5" y="5" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <rect x="5" y="8.5" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <rect x="5" y="12" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <circle cx="15" cy="5.75" r="0.8" fill="#a78bfa" />
                                    <circle cx="15" cy="9.25" r="0.8" fill="#a78bfa" />
                                    <circle cx="15" cy="12.75" r="0.8" fill="#a78bfa" />
                                </svg>
                            </div>
                            <h3 class="font-display text-lg font-700 text-white">Special Offer - Colocation 2U</h3>
                        </div>
                        <div class="mb-6">
                            <span class="font-display text-4xl font-800 text-white">Rp 1.500.000</span>
                            <span class="text-slate-500 text-sm">/bulan</span>
                        </div>
                        <ul class="space-y-3 mb-7">
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                1 Pair FO APJII Cyber 1st FL
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Power 200 VA
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Power Dual Sources
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Powe SUpport AC/DC
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                No-Rated Data Center
                            </li>
                        </ul>
                        <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                            class="w-full block text-center border border-violet-500/40 text-violet-400 hover:bg-violet-500/10 font-semibold px-5 py-3 rounded-xl transition-all duration-300 text-sm">
                            Order Sekarang
                        </a>
                    </article>
                    
                    <!-- Good Offer - Colocation Half Rack -->
                    <article class="glass-card rounded-2xl p-7 reveal" style="transition-delay:0.15s">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-lg bg-violet-500/15 flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <rect x="3" y="2" width="14" height="16" rx="1.5" stroke="#a78bfa"
                                        stroke-width="1.5" />
                                    <rect x="5" y="5" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <rect x="5" y="8.5" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <rect x="5" y="12" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <circle cx="15" cy="5.75" r="0.8" fill="#a78bfa" />
                                    <circle cx="15" cy="9.25" r="0.8" fill="#a78bfa" />
                                    <circle cx="15" cy="12.75" r="0.8" fill="#a78bfa" />
                                </svg>
                            </div>
                            <h3 class="font-display text-lg font-700 text-white">Good Offer - Colocation Half Rack</h3>
                        </div>
                        <div class="mb-6">
                            <span class="font-display text-4xl font-800 text-white">Rp 5.000.000</span>
                            <span class="text-slate-500 text-sm">/bulan</span>
                        </div>
                        <ul class="space-y-3 mb-7">
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                1 Pair FO APJII Cyber 1st FL
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Power 200 VA
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Power Dual Sources
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Powe SUpport AC/DC
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                No-Rated Data Center
                            </li>
                        </ul>
                        <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                            class="w-full block text-center border border-violet-500/40 text-violet-400 hover:bg-violet-500/10 font-semibold px-5 py-3 rounded-xl transition-all duration-300 text-sm">
                            Order Sekarang
                        </a>
                    </article>
                    
                    <!-- Best Offer - Colocation 1 Rack -->
                    <article class="glass-card rounded-2xl p-7 reveal" style="transition-delay:0.15s">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-lg bg-violet-500/15 flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <rect x="3" y="2" width="14" height="16" rx="1.5" stroke="#a78bfa"
                                        stroke-width="1.5" />
                                    <rect x="5" y="5" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <rect x="5" y="8.5" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <rect x="5" y="12" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <circle cx="15" cy="5.75" r="0.8" fill="#a78bfa" />
                                    <circle cx="15" cy="9.25" r="0.8" fill="#a78bfa" />
                                    <circle cx="15" cy="12.75" r="0.8" fill="#a78bfa" />
                                </svg>
                            </div>
                            <h3 class="font-display text-lg font-700 text-white">Best Offer - Colocation 1 Rack</h3>
                        </div>
                        <div class="mb-6">
                            <span class="font-display text-4xl font-800 text-white">Rp 7.000.000</span>
                            <span class="text-slate-500 text-sm">/bulan</span>
                        </div>
                        <ul class="space-y-3 mb-7">
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                1 Pair FO APJII Cyber 1st FL
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Power 200 VA
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Power Dual Sources
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Powe SUpport AC/DC
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                No-Rated Data Center
                            </li>
                        </ul>
                        <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                            class="w-full block text-center border border-violet-500/40 text-violet-400 hover:bg-violet-500/10 font-semibold px-5 py-3 rounded-xl transition-all duration-300 text-sm">
                            Order Sekarang
                        </a>
                    </article>
                    
                    <!-- Extra Bonus - Colocation Full Rack -->
                    <article class="glass-card rounded-2xl p-7 reveal" style="transition-delay:0.15s">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-lg bg-violet-500/15 flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <rect x="3" y="2" width="14" height="16" rx="1.5" stroke="#a78bfa"
                                        stroke-width="1.5" />
                                    <rect x="5" y="5" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <rect x="5" y="8.5" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <rect x="5" y="12" width="10" height="1.5" rx="0.75" fill="#a78bfa"
                                        opacity="0.5" />
                                    <circle cx="15" cy="5.75" r="0.8" fill="#a78bfa" />
                                    <circle cx="15" cy="9.25" r="0.8" fill="#a78bfa" />
                                    <circle cx="15" cy="12.75" r="0.8" fill="#a78bfa" />
                                </svg>
                            </div>
                            <h3 class="font-display text-lg font-700 text-white">Extra Bonus - Colocation Full Rack</h3>
                        </div>
                        <div class="mb-6">
                            <span class="font-display text-4xl font-800 text-white">Rp 8.000.000</span>
                            <span class="text-slate-500 text-sm">/bulan</span>
                        </div>
                        <ul class="space-y-3 mb-7">
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Setara Rate 3
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Free 1 pair FO APJII
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Free 1G Share Domistic Excahnge Network
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Free CAC Eyeball
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Free Smarthand
                            </li>
                            <li class="flex items-center gap-2.5 text-sm text-slate-200">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="flex-shrink-0">
                                    <path d="M3 8l3.5 3.5L13 4" stroke="#22c55e" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Support Onsite 24/7
                            </li>
                        </ul>
                        <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                            class="w-full block text-center border border-violet-500/40 text-violet-400 hover:bg-violet-500/10 font-semibold px-5 py-3 rounded-xl transition-all duration-300 text-sm">
                            Order Sekarang
                        </a>
                    </article>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════ INFRASTRUKTUR ═══════════════════════════ -->
    <section id="infrastruktur" class="py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Text Left -->
                <div class="reveal">
                    <span class="text-blue-400 text-sm font-semibold tracking-widest uppercase mb-3 block">Fasilitas
                        World-Class</span>
                    <h2 class="font-display text-4xl lg:text-5xl font-800 text-white mb-6">
                        Desain & <span class="gradient-text">Infrastruktur</span> Terdepan
                    </h2>
                    <p class="text-slate-400 text-lg leading-relaxed mb-8">
                        Data center kami dirancang dengan standar internasional Tier III, memastikan keandalan dan
                        keamanan infrastruktur untuk bisnis Anda.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Infra item -->
                        <div
                            class="flex items-start gap-3 p-4 rounded-xl bg-white/02 border border-white/05 hover:border-blue-500/20 transition-colors">
                            <div
                                class="w-9 h-9 rounded-lg bg-blue-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M3 9h12M9 3v12" stroke="#60a5fa" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <circle cx="9" cy="9" r="6" stroke="#60a5fa"
                                        stroke-width="1.5" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm mb-1">Redundant Power</h4>
                                <p class="text-slate-500 text-xs leading-relaxed">UPS N+1 dengan generator backup
                                    diesel kapasitas besar, zero downtime.</p>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-3 p-4 rounded-xl bg-white/02 border border-white/05 hover:border-cyan-500/20 transition-colors">
                            <div
                                class="w-9 h-9 rounded-lg bg-cyan-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M3 12c0-5 2-7 6-7s6 2 6 7" stroke="#22d3ee" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <path d="M6 12c0-2 1.3-3 3-3s3 1 3 3" stroke="#22d3ee" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <circle cx="9" cy="14" r="1.5" fill="#22d3ee" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm mb-1">Precision Cooling</h4>
                                <p class="text-slate-500 text-xs leading-relaxed">Sistem pendingin presisi N+1 menjaga
                                    suhu server di rentang optimal.</p>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-3 p-4 rounded-xl bg-white/02 border border-white/05 hover:border-red-500/20 transition-colors">
                            <div
                                class="w-9 h-9 rounded-lg bg-red-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M9 2L3 6v4c0 4.5 2.5 8.5 6 9.5 3.5-1 6-5 6-9.5V6L9 2z" stroke="#f87171"
                                        stroke-width="1.5" stroke-linejoin="round" />
                                    <path d="M6.5 10.5l2 2 3-4" stroke="#f87171" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm mb-1">Fire Protection</h4>
                                <p class="text-slate-500 text-xs leading-relaxed">Sistem pendeteksi dan pemadam
                                    kebakaran FM-200 yang ramah lingkungan.</p>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-3 p-4 rounded-xl bg-white/02 border border-white/05 hover:border-green-500/20 transition-colors">
                            <div
                                class="w-9 h-9 rounded-lg bg-green-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <rect x="4" y="8" width="10" height="8" rx="1.5" stroke="#4ade80"
                                        stroke-width="1.5" />
                                    <path d="M6.5 8V6a2.5 2.5 0 015 0v2" stroke="#4ade80" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <circle cx="9" cy="12.5" r="1" fill="#4ade80" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm mb-1">Security System</h4>
                                <p class="text-slate-500 text-xs leading-relaxed">Akses biometrik, CCTV 24/7, security
                                    guard, dan audit trail lengkap.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Right -->
                <div class="reveal relative flex justify-center" style="transition-delay:0.2s">
                    <div
                        class="absolute inset-0 bg-gradient-radial from-blue-500/10 to-transparent pointer-events-none">
                    </div>
                    <!-- Data center floor plan illustration -->
                    <svg viewBox="0 0 420 380" width="420" height="380" xmlns="http://www.w3.org/2000/svg"
                        class="drop-shadow-2xl animate-float-slow" aria-label="Data center floor plan illustration"
                        role="img">
                        <defs>
                            <linearGradient id="floorGrad" x1="0" y1="0" x2="1"
                                y2="1">
                                <stop offset="0%" stop-color="#0b1530" />
                                <stop offset="100%" stop-color="#060d1f" />
                            </linearGradient>
                            <linearGradient id="rackRowGrad" x1="0" y1="0" x2="0"
                                y2="1">
                                <stop offset="0%" stop-color="#1e40af" stop-opacity="0.8" />
                                <stop offset="100%" stop-color="#1e3a8a" stop-opacity="0.4" />
                            </linearGradient>
                        </defs>

                        <!-- Floor -->
                        <rect x="10" y="10" width="400" height="360" rx="16" fill="url(#floorGrad)"
                            stroke="#1e3a8a" stroke-width="1" stroke-opacity="0.5" />

                        <!-- Grid floor pattern -->
                        <g stroke="#1e40af" stroke-width="0.3" stroke-opacity="0.2">
                            <line x1="70" y1="10" x2="70" y2="370" />
                            <line x1="130" y1="10" x2="130" y2="370" />
                            <line x1="190" y1="10" x2="190" y2="370" />
                            <line x1="250" y1="10" x2="250" y2="370" />
                            <line x1="310" y1="10" x2="310" y2="370" />
                            <line x1="370" y1="10" x2="370" y2="370" />
                            <line x1="10" y1="70" x2="410" y2="70" />
                            <line x1="10" y1="130" x2="410" y2="130" />
                            <line x1="10" y1="190" x2="410" y2="190" />
                            <line x1="10" y1="250" x2="410" y2="250" />
                            <line x1="10" y1="310" x2="410" y2="310" />
                        </g>

                        <!-- Rack rows -->
                        <rect x="30" y="30" width="180" height="50" rx="6"
                            fill="url(#rackRowGrad)" stroke="#2563eb" stroke-width="1" />
                        <text x="40" y="50" font-size="8" fill="#60a5fa" font-family="monospace"
                            opacity="0.8">ROW A — ZONE 1</text>
                        <g fill="#22c55e">
                            <circle cx="180" cy="48" r="3" class="rack-light" />
                            <circle cx="170" cy="48" r="3" class="rack-light-2" />
                            <circle cx="160" cy="48" r="3" class="rack-light-3" />
                            <circle cx="150" cy="48" r="3" class="rack-light" />
                        </g>

                        <rect x="30" y="90" width="180" height="50" rx="6"
                            fill="url(#rackRowGrad)" stroke="#2563eb" stroke-width="1" />
                        <text x="40" y="110" font-size="8" fill="#60a5fa" font-family="monospace"
                            opacity="0.8">ROW B — ZONE 1</text>
                        <g fill="#22c55e">
                            <circle cx="180" cy="108" r="3" class="rack-light-2" />
                            <circle cx="170" cy="108" r="3" class="rack-light" />
                            <circle cx="160" cy="108" r="3" class="rack-light-3" />
                        </g>

                        <rect x="30" y="160" width="180" height="50" rx="6"
                            fill="url(#rackRowGrad)" stroke="#2563eb" stroke-width="1" />
                        <text x="40" y="180" font-size="8" fill="#60a5fa" font-family="monospace"
                            opacity="0.8">ROW C — ZONE 2</text>
                        <g fill="#facc15">
                            <circle cx="180" cy="178" r="3" class="rack-light" />
                            <circle cx="170" cy="178" r="3" class="rack-light-3" />
                        </g>
                        <g fill="#22c55e">
                            <circle cx="160" cy="178" r="3" class="rack-light-2" />
                            <circle cx="150" cy="178" r="3" class="rack-light" />
                        </g>

                        <rect x="30" y="230" width="180" height="50" rx="6"
                            fill="url(#rackRowGrad)" stroke="#2563eb" stroke-width="1" />
                        <text x="40" y="250" font-size="8" fill="#60a5fa" font-family="monospace"
                            opacity="0.8">ROW D — ZONE 2</text>

                        <rect x="30" y="300" width="180" height="50" rx="6"
                            fill="url(#rackRowGrad)" stroke="#2563eb" stroke-width="1" />
                        <text x="40" y="320" font-size="8" fill="#60a5fa" font-family="monospace"
                            opacity="0.8">ROW E — ZONE 3</text>

                        <!-- Right side: Network + Power room -->
                        <rect x="230" y="30" width="165" height="120" rx="6" fill="#06111e"
                            stroke="#1e3a8a" stroke-width="1" />
                        <text x="245" y="55" font-size="9" fill="#a78bfa" font-family="monospace">NETWORK
                            ROOM</text>
                        <!-- Router/Switch racks -->
                        <rect x="240" y="65" width="60" height="70" rx="4" fill="#0b1a35"
                            stroke="#2563eb" stroke-width="0.5" />
                        <rect x="310" y="65" width="60" height="70" rx="4" fill="#0b1a35"
                            stroke="#2563eb" stroke-width="0.5" />
                        <text x="250" y="82" font-size="6" fill="#60a5fa" font-family="monospace">CORE SW</text>
                        <text x="318" y="82" font-size="6" fill="#60a5fa" font-family="monospace">CORE SW</text>

                        <!-- Power room -->
                        <rect x="230" y="165" width="165" height="120" rx="6" fill="#06111e"
                            stroke="#1e3a8a" stroke-width="1" />
                        <text x="245" y="190" font-size="9" fill="#fbbf24" font-family="monospace">POWER
                            ROOM</text>
                        <rect x="240" y="200" width="70" height="70" rx="4" fill="#0c1a10"
                            stroke="#22c55e" stroke-width="0.5" />
                        <text x="248" y="220" font-size="6" fill="#4ade80" font-family="monospace">UPS N+1</text>
                        <rect x="320" y="200" width="70" height="70" rx="4" fill="#0c1a10"
                            stroke="#22c55e" stroke-width="0.5" />
                        <text x="328" y="220" font-size="6" fill="#4ade80" font-family="monospace">GEN SET</text>

                        <!-- Cooling -->
                        <rect x="230" y="300" width="165" height="50" rx="6" fill="#060f1a"
                            stroke="#22d3ee" stroke-width="0.8" />
                        <text x="245" y="323" font-size="8" fill="#22d3ee" font-family="monospace">❄ COOLING
                            SYSTEM</text>

                        <!-- Arrows/paths between zones -->
                        <path d="M210 55 L230 55" stroke="#2563eb" stroke-width="1" stroke-dasharray="3,3"
                            opacity="0.4" />
                        <path d="M210 115 L230 115" stroke="#2563eb" stroke-width="1" stroke-dasharray="3,3"
                            opacity="0.4" />

                        <!-- Status legend -->
                        <g transform="translate(30, 360)">
                            <circle cx="5" cy="5" r="3" fill="#22c55e" />
                            <text x="12" y="8" font-size="7" fill="#64748b" font-family="monospace">Online</text>
                            <circle cx="55" cy="5" r="3" fill="#facc15" />
                            <text x="62" y="8" font-size="7" fill="#64748b"
                                font-family="monospace">Warning</text>
                        </g>

                        <!-- Title -->
                        <text x="410" y="30" font-size="10" fill="#334155" font-family="monospace"
                            text-anchor="end">LDX Data Center — FLOOR PLAN</text>
                    </svg>

                    <!-- Tier badge -->
                    <div
                        class="absolute -bottom-4 left-1/2 -translate-x-1/2 glass-card px-5 py-3 rounded-xl flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8 1l2 4.5h4.5L11 8.5l1.5 5L8 11l-4.5 2.5L5 8.5 1.5 5.5H6L8 1z"
                                    stroke="#60a5fa" stroke-width="1" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-white text-xs font-semibold">Certified Tier III</p>
                            <p class="text-slate-500 text-xs">Uptime Institute</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════ SMART HANDS & POWER ═══════════════════════════ -->
    <section class="py-24 lg:py-32 relative">
        <div class="absolute inset-0 grid-pattern opacity-40 pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-14 reveal">
                <span class="text-blue-400 text-sm font-semibold tracking-widest uppercase mb-3 block">Layanan
                    Tambahan</span>
                <h2 class="font-display text-4xl lg:text-5xl font-800 text-white mb-4">
                    Smart Hand & <span class="gradient-text">Power Redundancy</span>
                </h2>
                <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                    Dukungan teknis on-site dan infrastruktur power yang tidak pernah mati untuk bisnis yang terus
                    berjalan.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Smart Hands -->
                <article class="glass-card rounded-2xl p-7 reveal">
                    <div class="flex items-start gap-4 mb-6">
                        <div
                            class="w-12 h-12 rounded-xl bg-blue-500/15 border border-blue-500/20 flex items-center justify-center flex-shrink-0">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"
                                aria-hidden="true">
                                <path d="M11 2C6 2 2 6 2 11s4 9 9 9 9-4 9-9-4-9-9-9z" stroke="#60a5fa"
                                    stroke-width="1.5" />
                                <path d="M8 11.5l2 2 4-4" stroke="#60a5fa" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-display text-xl font-700 text-white mb-1">Smart Hands</h3>
                            <p class="text-slate-400 text-sm">Teknisi terlatih on-site siap membantu kapanpun.</p>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#60a5fa" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            Instalasi & konfigurasi hardware server
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#60a5fa" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            Remote hands untuk troubleshooting on-site
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#60a5fa" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            Manajemen kabel dan patch panel
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#60a5fa" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            Pemasangan dan penggantian komponen
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#60a5fa" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            Dokumentasi visual dan laporan harian
                        </li>
                    </ul>
                </article>

                <!-- Power Redundancy -->
                <article class="glass-card rounded-2xl p-7 reveal" style="transition-delay:0.1s">
                    <div class="flex items-start gap-4 mb-6">
                        <div
                            class="w-12 h-12 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center flex-shrink-0">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"
                                aria-hidden="true">
                                <path d="M13 2L4 13h7l-2 7 9-11h-7l2-7z" stroke="#fbbf24" stroke-width="1.5"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-display text-xl font-700 text-white mb-1">Power Redundancy</h3>
                            <p class="text-slate-400 text-sm">Zero downtime dengan sistem power backup berlapis.</p>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#fbbf24" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            UPS online double-conversion N+1
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#fbbf24" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            Generator diesel auto-start &lt;10 detik
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#fbbf24" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            Dual power feed dari 2 substation berbeda
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#fbbf24" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            PDU intelligent per rack monitoring
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <div
                                class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <path d="M2 5l2 2 4-4" stroke="#fbbf24" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            PUE (Power Usage Effectiveness) &lt; 1.5
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════ PRICING HIGHLIGHT ═══════════════════════════ -->
    <section class="py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal rounded-2xl overflow-hidden"
                style="background: linear-gradient(135deg, rgba(30,64,175,0.2) 0%, rgba(109,40,217,0.15) 100%); border: 1px solid rgba(37,99,235,0.3);">
                <div class="p-8 lg:p-12">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-500 to-violet-500 text-white text-xs font-700 font-display px-3 py-1.5 rounded-full mb-4">
                                🔥 Limited Offer
                            </div>
                            <h2 class="font-display text-3xl lg:text-4xl font-800 text-white mb-3">
                                Mulai dari <span class="gradient-text">Rp 1.000.000</span><span
                                    class="text-slate-400 text-xl font-400">/bulan</span>
                            </h2>
                            <p class="text-slate-400 text-base leading-relaxed mb-6">
                                Paket Location 1U — harga terjangkau untuk colocation server, rack server murah
                                berkualitas enterprise di data center Indonesia terpercaya.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="#paket"
                                    class="btn-primary text-white font-semibold px-6 py-3 rounded-xl relative z-10 text-sm">Mulai
                                    Sekarang</a>
                                <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                                    class="text-white font-semibold px-6 py-3 rounded-xl border border-white/15 hover:border-white/30 transition-all text-sm">Konsultasi
                                    Gratis</a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/04 rounded-xl p-4 border border-white/06">
                                <p class="text-2xl font-display font-700 text-white">1U</p>
                                <p class="text-slate-400 text-xs mt-1">Rp 1.000.000K/bulan</p>
                            </div>
                            <div class="bg-white/04 rounded-xl p-4 border border-blue-500/20">
                                <p class="text-2xl font-display font-700 text-white">Half</p>
                                <p class="text-slate-400 text-xs mt-1">Rp 5Jt/bulan</p>
                            </div>
                            <div class="bg-white/04 rounded-xl p-4 border border-white/06">
                                <p class="text-2xl font-display font-700 text-white">Full</p>
                                <p class="text-slate-400 text-xs mt-1">Rp 7Jt/bulan</p>
                            </div>
                            <div class="bg-white/04 rounded-xl p-4 border border-violet-500/20">
                                <p class="text-2xl font-display font-700 text-white">Custom</p>
                                <p class="text-slate-400 text-xs mt-1">Hubungi kami</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════ FAQ ═══════════════════════════ -->
    <section id="faq" class="py-24 lg:py-32">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-blue-400 text-sm font-semibold tracking-widest uppercase mb-3 block">FAQ</span>
                <h2 class="font-display text-4xl lg:text-5xl font-800 text-white mb-4">
                    Pertanyaan yang <span class="gradient-text">Sering Diajukan</span>
                </h2>
                <p class="text-slate-400 text-lg">Temukan jawaban seputar layanan colocation server dan data center
                    kami.</p>
            </div>

            <div class="space-y-3" id="faqContainer">
                <!-- FAQ items -->
                <div class="faq-item glass-card rounded-xl overflow-hidden reveal" data-index="0">
                    <button class="faq-trigger w-full flex items-center justify-between p-5 text-left gap-4"
                        aria-expanded="false">
                        <span class="text-white font-semibold text-sm lg:text-base">Apa itu colocation server dan
                            bagaimana cara kerjanya?</span>
                        <span class="faq-icon text-blue-400 flex-shrink-0 text-xl font-light leading-none">+</span>
                    </button>
                    <div class="faq-content px-5">
                        <p class="text-slate-400 text-sm leading-relaxed pb-5">Colocation server adalah layanan di
                            mana Anda menempatkan server fisik milik Anda sendiri di fasilitas data center kami. Kami
                            menyediakan ruang rack, power listrik, koneksi internet berkecepatan tinggi, sistem
                            pendingin, keamanan fisik, dan monitoring 24/7. Ini berbeda dengan cloud hosting karena
                            hardware servernya adalah milik Anda, memberikan kontrol penuh atas perangkat keras.</p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-xl overflow-hidden reveal" data-index="1">
                    <button class="faq-trigger w-full flex items-center justify-between p-5 text-left gap-4"
                        aria-expanded="false">
                        <span class="text-white font-semibold text-sm lg:text-base">Berapa lama proses setup untuk
                            colocation rack server baru?</span>
                        <span class="faq-icon text-blue-400 flex-shrink-0 text-xl font-light leading-none">+</span>
                    </button>
                    <div class="faq-content px-5">
                        <p class="text-slate-400 text-sm leading-relaxed pb-5">Proses setup colocation server kami
                            sangat cepat. Setelah pembayaran dikonfirmasi dan dokumen dilengkapi, server Anda dapat
                            langsung ditempatkan di fasilitas kami. Untuk paket 1U dan Half Rack, biasanya selesai dalam
                            1-2 hari kerja. Paket Full Rack dengan konfigurasi khusus memerlukan 3-5 hari kerja. Tim
                            Smart Hands kami siap membantu instalasi dari awal.</p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-xl overflow-hidden reveal" data-index="2">
                    <button class="faq-trigger w-full flex items-center justify-between p-5 text-left gap-4"
                        aria-expanded="false">
                        <span class="text-white font-semibold text-sm lg:text-base">Apakah ada jaminan uptime untuk
                            layanan hosting server dan data center?</span>
                        <span class="faq-icon text-blue-400 flex-shrink-0 text-xl font-light leading-none">+</span>
                    </button>
                    <div class="faq-content px-5">
                        <p class="text-slate-400 text-sm leading-relaxed pb-5">Ya, kami memberikan garansi uptime
                            99.99% per tahun berdasarkan SLA (Service Level Agreement) yang terdokumentasi. Ini berarti
                            downtime maksimal hanya sekitar 52 menit per tahun. Jika terjadi downtime melebihi SLA yang
                            disepakati karena kesalahan dari pihak kami, Anda akan mendapatkan kompensasi kredit sesuai
                            ketentuan yang tercantum dalam kontrak.</p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-xl overflow-hidden reveal" data-index="3">
                    <button class="faq-trigger w-full flex items-center justify-between p-5 text-left gap-4"
                        aria-expanded="false">
                        <span class="text-white font-semibold text-sm lg:text-base">Apakah saya bisa mengakses server
                            saya kapan saja di data center Indonesia ini?</span>
                        <span class="faq-icon text-blue-400 flex-shrink-0 text-xl font-light leading-none">+</span>
                    </button>
                    <div class="faq-content px-5">
                        <p class="text-slate-400 text-sm leading-relaxed pb-5">Akses fisik ke data center tersedia
                            24/7 namun dengan prosedur keamanan yang ketat. Anda perlu mendaftarkan identitas dan
                            mendapatkan akses kartu terlebih dahulu. Setiap kunjungan akan tercatat dalam sistem audit
                            trail kami. Untuk akses remote, kami menyediakan layanan Remote KVM (untuk paket Half Rack
                            ke atas) sehingga Anda dapat mengakses server dari mana saja tanpa harus datang ke lokasi.
                        </p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-xl overflow-hidden reveal" data-index="4">
                    <button class="faq-trigger w-full flex items-center justify-between p-5 text-left gap-4"
                        aria-expanded="false">
                        <span class="text-white font-semibold text-sm lg:text-base">Apa perbedaan antara rack server
                            murah vs paket premium di LDX Data Center?</span>
                        <span class="faq-icon text-blue-400 flex-shrink-0 text-xl font-light leading-none">+</span>
                    </button>
                    <div class="faq-content px-5">
                        <p class="text-slate-400 text-sm leading-relaxed pb-5">Semua paket kami menggunakan
                            infrastruktur yang sama — fasilitas Tier III bersertifikat dengan keamanan dan power
                            redundancy identik. Perbedaannya terletak pada jumlah ruang rack (1U, 21U, 42U), kapasitas
                            power, bandwidth yang tersedia, jumlah IP public, dan inklusivitas layanan Smart Hands.
                            Paket yang lebih besar memberikan lebih banyak fleksibilitas dan layanan yang lebih
                            komprehensif dengan biaya per-unit yang lebih efisien.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════ CTA BANNER ═══════════════════════════ -->
    <section class="py-16 lg:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="relative rounded-3xl overflow-hidden reveal"
                style="background: linear-gradient(135deg, #1e40af 0%, #4c1d95 50%, #1e3a8a 100%);">
                <!-- Pattern overlay -->
                <div class="absolute inset-0 grid-pattern opacity-20"></div>
                <!-- Glow orbs -->
                <div class="absolute top-0 left-1/4 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-violet-400/20 rounded-full blur-3xl"></div>

                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10 p-10 lg:p-16">
                    <div class="text-center lg:text-left">
                        <h2 class="font-display text-4xl lg:text-5xl font-800 text-white mb-4 leading-tight">
                            Siap Memindahkan<br />Server Anda ke<br /><span
                                style="background: linear-gradient(135deg, #93c5fd, #c4b5fd); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Level
                                Berikutnya?</span>
                        </h2>
                        <p class="text-blue-200 text-lg max-w-lg">
                            Bergabung dengan 500+ perusahaan terpercaya yang sudah mempercayakan infrastruktur mereka
                            kepada LDX Data Center.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 flex-shrink-0">
                        <a href="#paket"
                            class="bg-white text-blue-900 font-700 font-display px-8 py-4 rounded-xl text-base hover:bg-blue-50 transition-colors whitespace-nowrap text-center">
                            Mulai Sekarang
                        </a>
                        <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                            class="border border-white/30 text-white font-semibold px-8 py-4 rounded-xl text-base hover:bg-white/10 transition-colors whitespace-nowrap text-center">
                            Hubungi Sales
                        </a>
                    </div>
                </div>

                <!-- Decorative server illustration -->
                <div class="absolute right-8 top-1/2 -translate-y-1/2 opacity-10 hidden xl:block">
                    <svg viewBox="0 0 120 200" width="120" height="200" xmlns="http://www.w3.org/2000/svg">
                        <rect x="10" y="10" width="100" height="180" rx="8" fill="white" />
                        <rect x="18" y="20" width="84" height="18" rx="3" fill="none"
                            stroke="white" stroke-width="2" />
                        <rect x="18" y="44" width="84" height="18" rx="3" fill="none"
                            stroke="white" stroke-width="2" />
                        <rect x="18" y="68" width="84" height="18" rx="3" fill="none"
                            stroke="white" stroke-width="2" />
                        <rect x="18" y="92" width="84" height="18" rx="3" fill="none"
                            stroke="white" stroke-width="2" />
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════ FOOTER ═══════════════════════════ -->
    <footer id="kontak" class="border-t border-white/06 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 mb-12">
                <!-- Brand -->
                <div class="col-span-2 lg:col-span-1">
                    <a href="#" class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center relative z-10">
                            <img src="{{ asset('assets/logos/ldx-box.png') }}" alt="logo">
                        </div>
                        <span class="font-display font-800 text-white text-xl tracking-tight">LDX<span
                                class="gradient-text">Data Center</span></span>
                    </a>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">Data center dan colocation server
                        terpercaya di Indonesia. Infrastruktur enterprise untuk semua ukuran bisnis.</p>
                    <address class="not-italic text-slate-500 text-sm space-y-1.5">
                        <p class="flex items-start gap-2">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                class="flex-shrink-0 mt-0.5">
                                <path d="M7 1C4.8 1 3 2.8 3 5c0 3.3 4 8 4 8s4-4.7 4-8c0-2.2-1.8-4-4-4z"
                                    stroke="#60a5fa" stroke-width="1" />
                                <circle cx="7" cy="5" r="1.5" stroke="#60a5fa"
                                    stroke-width="1" />
                            </svg>
                            Ruko Gardenia Blok E No. 23 Desa Rajeg Mulia, Kec. Rajeg, Kab. Tangera
                            <br> Gedung Cyber 1- Lt. 10
                        </p>
                        <p class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path
                                    d="M1 3.5A1.5 1.5 0 012.5 2h9A1.5 1.5 0 0113 3.5v7A1.5 1.5 0 0111.5 12h-9A1.5 1.5 0 011 10.5v-7z"
                                    stroke="#60a5fa" stroke-width="1" />
                                <path d="M1 4l6 4 6-4" stroke="#60a5fa" stroke-width="1" />
                            </svg>
                            <a href="mailto:info@ldxdc.id"
                                class="hover:text-blue-400 transition-colors">info@ldxdc.id</a>
                        </p>
                        <p class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path
                                    d="M2 2.5a.5.5 0 01.5-.5H4a.5.5 0 01.5.4l.6 3a.5.5 0 01-.3.5L3.6 6.5a9 9 0 004 4l.6-1.2a.5.5 0 01.5-.3l3 .6a.5.5 0 01.4.5V11.5a.5.5 0 01-.5.5H11A9.5 9.5 0 011.5 3 .5.5 0 012 2.5z"
                                    stroke="#60a5fa" stroke-width="1" />
                            </svg>
                            <a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                                class="hover:text-blue-400 transition-colors">+62 811 8114 1746</a>
                        </p>
                    </address>
                </div>

                <!-- Paket -->
                <div>
                    <h4 class="font-display font-700 text-white text-sm mb-4 uppercase tracking-wide">Paket Colocation
                    </h4>
                    <ul class="space-y-2.5">
                        <li><a href="#paket"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Sub Rack / 1U</a>
                        </li>
                        <li><a href="#paket"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Half Rack
                                (21U)</a></li>
                        <li><a href="#paket"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Full Rack
                                (42U)</a></li>
                        <li><a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Custom
                                Enterprise</a></li>
                        <li><a href="https://api.whatsapp.com/send/?phone=%2B6281181141746&text&type=phone_number&app_absent=0"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Smart Hands</a>
                        </li>
                    </ul>
                </div>

                <!-- Fitur -->
                <div>
                    <h4 class="font-display font-700 text-white text-sm mb-4 uppercase tracking-wide">Fitur</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#features"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Cross Connect</a>
                        </li>
                        <li><a href="#features"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Real-Time
                                Monitoring</a></li>
                        <li><a href="#features"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Power
                                Redundancy</a></li>
                        <li><a href="#features"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Security
                                System</a></li>
                        <li><a href="#features"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">NOC 24/7</a></li>
                    </ul>
                </div>

                <!-- Social & Links -->
                <div>
                    <h4 class="font-display font-700 text-white text-sm mb-4 uppercase tracking-wide">Social Media
                    </h4>
                    <div class="flex gap-3 mb-6">
                        <a href="#" aria-label="Twitter/X LDX Data Center"
                            class="w-9 h-9 rounded-lg glass flex items-center justify-center hover:border-blue-500/40 transition-colors">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M1 1l5 5.5L1 12h2l3.7-4.1L10 12h3L8 6.2 12.8 1h-2L7 4.9 3.5 1H1z"
                                    fill="#94a3b8" />
                            </svg>
                        </a>
                        <a href="#" aria-label="LinkedIn LDX Data Center"
                            class="w-9 h-9 rounded-lg glass flex items-center justify-center hover:border-blue-500/40 transition-colors">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <rect x="1" y="1" width="12" height="12" rx="2" stroke="#94a3b8"
                                    stroke-width="1" />
                                <path d="M4 6v4M4 4.5v.01M7 10V7.5c0-.8.7-1.5 1.5-1.5s1.5.7 1.5 1.5V10"
                                    stroke="#94a3b8" stroke-width="1" stroke-linecap="round" />
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram LDX Data Center"
                            class="w-9 h-9 rounded-lg glass flex items-center justify-center hover:border-blue-500/40 transition-colors">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <rect x="1.5" y="1.5" width="11" height="11" rx="3"
                                    stroke="#94a3b8" stroke-width="1" />
                                <circle cx="7" cy="7" r="2.5" stroke="#94a3b8"
                                    stroke-width="1" />
                                <circle cx="10.5" cy="3.5" r="0.6" fill="#94a3b8" />
                            </svg>
                        </a>
                    </div>
                    <h4 class="font-display font-700 text-white text-sm mb-3 uppercase tracking-wide">Lainnya</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Network
                                Status</a></li>
                        <li><a href="#"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">Dokumentasi</a>
                        </li>
                        <li><a href="#"
                                class="text-slate-500 text-sm hover:text-blue-400 transition-colors">SLA & Terms</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom bar -->
            <div class="border-t border-white/06 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-slate-600 text-xs">© 2025 LDX Data Center. All rights reserved. — Data Center Indonesia
                    terpercaya.</p>
                <div class="flex gap-4">
                    <a href="#" class="text-slate-600 text-xs hover:text-slate-400 transition-colors">Privacy
                        Policy</a>
                    <a href="#" class="text-slate-600 text-xs hover:text-slate-400 transition-colors">Terms of
                        Service</a>
                    <a href="#" class="text-slate-600 text-xs hover:text-slate-400 transition-colors">Cookie
                        Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // ── Navbar scroll effect ──
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        }, {
            passive: true
        });

        // ── Mobile menu ──
        const hamburger = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobileMenu');
        hamburger.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.toggle('open');
            hamburger.classList.toggle('open', isOpen);
            hamburger.setAttribute('aria-expanded', isOpen);
        });
        document.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('open');
                hamburger.classList.remove('open');
            });
        });

        // ── Scroll reveal ──
        const reveals = document.querySelectorAll('.reveal');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    observer.unobserve(e.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -60px 0px'
        });
        reveals.forEach(el => observer.observe(el));

        // ── FAQ accordion ──
        document.querySelectorAll('.faq-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = btn.closest('.faq-item');
                const content = item.querySelector('.faq-content');
                const isOpen = item.classList.contains('open');

                // Close all
                document.querySelectorAll('.faq-item').forEach(i => {
                    i.classList.remove('open');
                    i.querySelector('.faq-content').classList.remove('open');
                    i.querySelector('.faq-trigger').setAttribute('aria-expanded', 'false');
                });

                // Open clicked (if was closed)
                if (!isOpen) {
                    item.classList.add('open');
                    content.classList.add('open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });

        // ── Smooth anchor scroll ──
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const href = a.getAttribute('href');
                if (href === '#') return;
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
    </script>
</body>

</html>
