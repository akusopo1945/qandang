<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Qandang - Smart Livestock Monitoring</title>
    <meta name="description" content="Qandang adalah platform smart farming untuk monitoring ternak kambing digital dengan QR Code, marketplace, dan manajemen kandang modern.">
    <link rel="preload" as="image" href="{{ asset('images/banner-hero-mobile.webp') }}" imagesrcset="{{ asset('images/banner-hero-mobile.webp') }} 640w, {{ asset('images/banner-hero-tablet.webp') }} 1024w, {{ asset('images/banner-hero.webp') }} 2816w" imagesizes="(max-width: 640px) 100vw, (max-width: 1024px) 100vw, 50vw">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Qandang - Smart Livestock Monitoring">
    <meta property="og:description" content="Solusi Smart Farming modern untuk monitoring ternak kambing digital menggunakan QR Code.">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    </noscript>

    @vite('resources/css/app.css')

    <style>
        @keyframes scan {
            0%, 100% { top: 0%; opacity: 0.8; }
            50% { top: 100%; opacity: 0.8; }
        }
        .scan-line {
            animation: scan 3s infinite ease-in-out;
        }
        
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-400 { transition-delay: 400ms; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0.5deg); }
            50% { transform: translateY(-8px) rotate(-0.5deg); }
        }
        .float-anim {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 15px rgba(74, 103, 65, 0.1); }
            50% { box-shadow: 0 0 25px rgba(74, 103, 65, 0.3); }
        }
        .glow-btn {
            animation: pulse-glow 3s infinite ease-in-out;
        }
        
        .bg-grid {
            background-size: 20px 20px;
            background-image: 
                linear-gradient(to right, rgba(74, 103, 65, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(74, 103, 65, 0.1) 1px, transparent 1px);
        }
        
        #graph-path {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            transition: stroke-dashoffset 2s ease-out;
        }
        .reveal.active #graph-path {
            stroke-dashoffset: 0;
        }
    </style>
</head>
<body class="antialiased text-[#2d241e] bg-[#fdfaf5] font-sans selection:bg-[#4a6741] selection:text-white overflow-x-hidden">

    <!-- Preloader Screen -->
    <div id="preloader" class="fixed inset-0 z-[100] bg-[#4a6741] flex flex-col items-center justify-center transition-all duration-700 ease-out">
        <div class="flex flex-col items-center gap-6">
            <!-- Pulsing Logo -->
            <div class="relative w-28 h-28 bg-white rounded-3xl flex items-center justify-center p-4 border border-white animate-pulse shadow-2xl">
                <img src="{{ asset('images/logo.webp') }}" alt="Qandang Logo" width="120" height="120" class="w-20 h-20 object-contain" decoding="async">
                <div class="absolute inset-0 rounded-3xl border-2 border-orange-400 animate-ping opacity-25"></div>
            </div>
            
            <div class="text-center">
                <h2 class="text-white font-extrabold text-xl tracking-wider uppercase">Qandang</h2>
                <p class="text-white/60 text-[10px] mt-1 font-medium tracking-widest uppercase">Smart Farming Platform</p>
            </div>
            
            <!-- Loading Progress Bar -->
            <div class="w-40 h-1 bg-white/20 rounded-full overflow-hidden relative">
                <div class="absolute left-0 top-0 bottom-0 bg-orange-400 rounded-full transition-all duration-700 ease-out" id="preloader-bar" style="width: 0%;"></div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 py-6">

        <div class="container mx-auto px-4 md:px-6">
            <nav class="relative flex items-center justify-between bg-white/80 backdrop-blur-xl border border-[#a67c52]/10 shadow-lg px-6 py-3 rounded-2xl md:rounded-[2rem]">

                <!-- Logo -->
                <a href="/" class="flex items-center gap-2 group">
                    <img src="{{ asset('images/logo.webp') }}" alt="Qandang Logo" width="450" height="450" class="h-10 md:h-12 w-auto transition-transform duration-500 group-hover:scale-105" decoding="async">
                    <span class="font-extrabold text-xl tracking-tight text-[#4a6741]">QANDANG</span>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('catalog') }}" class="text-sm font-bold uppercase tracking-wider hover:text-[#4a6741] transition-colors">Katalog</a>
                    <a href="#features" class="text-sm font-bold uppercase tracking-wider hover:text-[#4a6741] transition-colors">Fitur</a>
                    <a href="#about" class="text-sm font-bold uppercase tracking-wider hover:text-[#4a6741] transition-colors">Misi</a>
                    <a href="{{ route('wishlist.index') }}" class="relative text-sm font-bold uppercase tracking-wider hover:text-[#4a6741] flex items-center gap-2">
                        Wishlist
                        @if($wishlist_count > 0)
                            <span class="absolute -top-2 -right-4 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $wishlist_count }}</span>
                        @endif
                    </a>
                    <a href="{{ route('cart.index') }}" class="relative text-sm font-bold uppercase tracking-wider hover:text-[#4a6741] flex items-center gap-2">
                        Keranjang
                        @if($cart_count > 0)
                            <span class="absolute -top-2 -right-4 bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $cart_count }}</span>
                        @endif
                    </a>
                    <a href="/get-started" class="bg-[#4a6741] hover:bg-[#3a5233] text-white px-6 py-2.5 rounded-full font-bold text-sm uppercase tracking-wide shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                        Mulai Sekarang
                    </a>
                </div>

                <!-- Mobile Header Icons -->
                <div class="flex items-center gap-2 lg:hidden">
                    <a href="{{ route('wishlist.index') }}" class="relative p-2 text-[#2d241e] hover:text-[#4a6741]" aria-label="Wishlist">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        @if($wishlist_count > 0)
                            <span class="absolute top-1 right-1 bg-red-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full">{{ $wishlist_count }}</span>
                        @endif
                    </a>

                    <!-- Mobile Menu Toggle -->
                    <button type="button" data-mobile-menu-toggle aria-controls="welcome-mobile-menu" aria-expanded="false" class="p-2 text-[#2d241e]" aria-label="Buka Menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </nav>
        </div>

        <!-- Mobile Menu -->
        <div id="welcome-mobile-menu"
             data-mobile-menu
             hidden
             class="lg:hidden absolute top-full left-0 right-0 mt-4 px-4">
            <div class="bg-white border border-[#a67c52]/10 rounded-3xl shadow-2xl p-6 flex flex-col gap-4">
                <a data-mobile-menu-close href="{{ route('catalog') }}" class="text-lg font-bold p-2 hover:text-[#4a6741]">Katalog</a>
                <a data-mobile-menu-close href="{{ route('cart.index') }}" class="text-lg font-bold p-2 hover:text-[#4a6741] flex items-center justify-between">
                    Keranjang
                    @if($cart_count > 0)
                        <span class="bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $cart_count }}</span>
                    @endif
                </a>
                <a data-mobile-menu-close href="#features" class="text-lg font-bold p-2 hover:text-[#4a6741]">Fitur</a>
                <a data-mobile-menu-close href="#about" class="text-lg font-bold p-2 hover:text-[#4a6741]">Misi</a>
                <a data-mobile-menu-close href="#contact" class="text-lg font-bold p-2 hover:text-[#4a6741]">Kontak</a>
                <hr class="border-[#a67c52]/10">
                <a href="/get-started" class="bg-[#4a6741] text-white text-center py-4 rounded-2xl font-bold text-lg">
                    Mulai Sekarang
                </a>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden">
            <div class="container mx-auto px-4 relative z-10">
                <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-8">
                    <!-- Right Content: Visual Banner / Scanner Simulation -->
                    <div class="w-full lg:w-1/2 relative order-1 lg:order-2 float-anim">
                        <!-- Card Container -->
                        <div class="relative z-10 bg-white/70 backdrop-blur-xl rounded-[2.5rem] p-6 shadow-2xl shadow-[#4a6741]/10 border border-white/40">
                            <!-- Tabs Header -->
                            <div class="flex gap-2 mb-6 bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/50">
                                <button id="tab-promo-btn" class="flex-1 py-3 px-4 rounded-xl text-sm font-extrabold transition-all duration-300 bg-white shadow-sm text-[#4a6741]" onclick="switchHeroTab('promo')">
                                    🎁 Promo Spesial
                                </button>
                                <button id="tab-scan-btn" class="flex-1 py-3 px-4 rounded-xl text-sm font-extrabold transition-all duration-300 text-slate-600 hover:text-[#4a6741]" onclick="switchHeroTab('scan')">
                                    🔍 Demo Scan QR
                                </button>
                            </div>

                            <!-- Tab 1: Promo Panel -->
                            <div id="panel-promo" class="transition-opacity duration-500">
                                <div class="relative rounded-2xl overflow-hidden shadow-lg border-2 border-white">
                                    <picture>
                                        <source media="(max-width: 640px)" srcset="{{ asset('images/banner-hero-mobile.webp') }}" type="image/webp">
                                        <source media="(max-width: 1024px)" srcset="{{ asset('images/banner-hero-tablet.webp') }}" type="image/webp">
                                        <source srcset="{{ asset('images/banner-hero.webp') }}" type="image/webp">
                                        <img src="{{ asset('images/banner-hero.png') }}" alt="Promo Qandang Free T-Shirt" width="2816" height="1536" class="w-full h-auto object-cover" fetchpriority="high" decoding="async">
                                    </picture>
                                </div>
                            </div>

                            <!-- Tab 2: QR Scanner Panel -->
                            <div id="panel-scan" class="hidden transition-opacity duration-500">
                                <div class="relative flex flex-col sm:flex-row gap-6 items-center bg-[#fdfaf5]/85 p-6 rounded-2xl border border-[#a67c52]/10 min-h-[300px]">
                                    <!-- Scanner Interface -->
                                    <div class="relative w-44 h-44 bg-slate-900 rounded-xl overflow-hidden shadow-inner flex items-center justify-center border-2 border-[#4a6741]/30 shrink-0 mx-auto">
                                        <!-- Scanner Laser -->
                                        <div id="scan-laser" class="absolute left-0 right-0 h-1 bg-emerald-400 shadow-[0_0_10px_#34d399] scan-line z-20"></div>
                                        <!-- Mock Camera Grid -->
                                        <div class="absolute inset-0 bg-grid opacity-25"></div>
                                        <!-- QR Code Overlay -->
                                        <div id="mock-qr" class="relative z-10 w-28 h-28 bg-white p-2 rounded-lg transition-transform duration-500">
                                            <svg class="w-full h-full text-slate-800" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm13-2h3v2h-3v-2zm-2 2h2v2h-2v-2zm2 2h3v2h-3v-2zm-4 0h2v2h-2v-2zm2-4h2v2h-2v-2zm-4 0h2v2h-2v-2zm-2-2h2v2h-2v-2zm4-2h2v2h-2V7zm-2 2h2v2h-2V9zm-2 2h2v2h-2v-2zm6-4h2v2h-2V7z"/>
                                            </svg>
                                        </div>
                                        <!-- Success Check -->
                                        <div id="scan-success-icon" class="absolute inset-0 bg-[#4a6741]/90 flex items-center justify-center opacity-0 scale-50 transition-all duration-500 z-30">
                                            <svg class="w-12 h-12 text-white animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Live Result Panel -->
                                    <div class="flex-1 w-full text-left">
                                        <div id="scan-instruction" class="text-center sm:text-left py-2">
                                            <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Simulasi Smart QR</p>
                                            <p class="text-[#2d241e] font-extrabold text-base mb-4 leading-snug">Dekatkan kamera ke tag QR telinga kambing</p>
                                            <button onclick="triggerMockScan()" class="w-full sm:w-auto bg-[#4a6741] text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-[#3a5233] transition-colors shadow-lg shadow-[#4a6741]/20">
                                                Mulai Scanning
                                            </button>
                                        </div>

                                        <div id="scan-result" class="hidden">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <span class="bg-[#4a6741]/10 text-[#4a6741] border border-[#4a6741]/20 px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider">
                                                        Boer Premium
                                                    </span>
                                                    <h4 class="font-extrabold text-base text-[#2d241e] mt-1">Singo Edan</h4>
                                                    <p class="text-[10px] text-[#54483e] font-mono">QND-BOE-092</p>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-[9px] text-[#54483e] font-extrabold uppercase">Kondisi</span>
                                                    <p class="text-emerald-600 font-bold text-xs flex items-center justify-end gap-1">
                                                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                                        Sangat Sehat
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-2 border-y border-[#a67c52]/10 py-2.5 my-2.5">
                                                <div>
                                                    <span class="text-[8px] text-[#54483e] uppercase font-bold tracking-tighter block">Bobot</span>
                                                    <span class="font-bold text-[#2d241e] text-xs">64.5 kg</span>
                                                </div>
                                                <div>
                                                    <span class="text-[8px] text-[#54483e] uppercase font-bold tracking-tighter block">Vaksinasi</span>
                                                    <span class="font-bold text-[#2d241e] text-xs">Vaksin A (12 Jun)</span>
                                                </div>
                                                <div>
                                                    <span class="text-[8px] text-[#54483e] uppercase font-bold tracking-tighter block">Prediksi AI (30h)</span>
                                                    <span class="font-bold text-[#4a6741] text-xs">+5.2 kg 📈</span>
                                                </div>
                                                <div>
                                                    <span class="text-[8px] text-[#54483e] uppercase font-bold tracking-tighter block">Suhu IoT</span>
                                                    <span class="font-bold text-[#2d241e] text-xs">28.4°C (Normal)</span>
                                                </div>
                                            </div>

                                            <button onclick="resetMockScan()" class="text-[10px] font-bold text-slate-500 hover:text-[#4a6741] transition-colors flex items-center gap-1.5">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5" />
                                                </svg>
                                                Scan Ulang
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Badges -->
                        <div class="absolute -top-6 right-2 z-20 bg-orange-500 text-white px-6 py-3 rounded-2xl font-black text-sm uppercase tracking-tighter shadow-xl">
                            Terbatas!
                        </div>
                        <div class="absolute -bottom-6 -left-6 z-20 bg-white p-4 rounded-2xl shadow-xl flex items-center gap-3 border border-[#a67c52]/10">
                            <div class="w-10 h-10 bg-[#4a6741] rounded-full flex items-center justify-center text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-[#54483e] font-bold uppercase">Verified Farm</p>
                                <p class="text-sm font-extrabold text-[#2d241e]">100% Trusted</p>
                            </div>
                        </div>
                    </div>

                    <!-- Left Content: Copywriting -->
                    <div class="w-full lg:w-1/2 text-left order-2 lg:order-1 reveal">
                        <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-[#2d241e] leading-[1.1] mb-6 tracking-tight">
                            Modernisasi Kandang,<br>Dapatkan <span class="text-[#4a6741] relative">Rewardnya!<span class="absolute bottom-1 left-0 w-full h-3 bg-[#4a6741]/10 -z-10"></span></span>
                        </h1>

                        <p class="text-lg md:text-xl text-[#54483e] mb-8 leading-relaxed max-w-xl">
                            Urus ternak lebih mudah dengan sistem digital. Daftarkan peternakanmu bulan ini dan nikmati berbagai keuntungan eksklusif.
                        </p>

                        <!-- Benefits List -->
                        <ul class="space-y-4 mb-10">
                            <li class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-[#4a6741] text-white rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="font-bold text-[#2d241e]">FREE Official Qandang T-Shirt (Limited Edition)</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-[#4a6741] text-white rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="font-bold text-[#2d241e]">FREE Akses Aplikasi Selamanya</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-[#4a6741] text-white rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="font-bold text-[#2d241e]">Prioritas Pemasaran Kambing di Marketplace</span>
                            </li>
                        </ul>

                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <a href="/get-started" class="w-full sm:w-auto bg-[#4a6741] hover:bg-[#3a5233] text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-xl shadow-[#4a6741]/20 transition-all transform hover:-translate-y-1 text-center glow-btn">
                                Daftar Sekarang - GRATIS
                            </a>
                            <a href="#catalog" class="w-full sm:w-auto flex items-center justify-center gap-2 px-10 py-4 font-bold text-lg hover:text-[#4a6741] transition-colors">
                                Lihat Kambing Unggulan
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Background Elements -->
            <div class="absolute top-1/4 -left-20 w-64 h-64 bg-[#4a6741]/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-[#a67c52]/5 rounded-full blur-3xl"></div>
        </section>

        <!-- Catalog Section -->
        <section id="catalog" class="py-24 bg-white rounded-[3rem] md:rounded-[5rem] mx-2 md:mx-6 shadow-sm border border-[#a67c52]/5 reveal">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 bg-[#4a6741]/10 px-4 py-1.5 rounded-full mb-4 border border-[#4a6741]/20">
                            <span class="text-xs font-bold text-[#4a6741] uppercase tracking-widest">Marketplace</span>
                        </div>
                        <h2 class="text-3xl md:text-5xl font-extrabold text-[#2d241e]">Katalog Kambing Terkini</h2>
                    </div>
                    <a href="{{ route('catalog') }}" class="group flex items-center gap-2 font-bold text-[#4a6741] hover:text-[#3a5233] transition-colors">
                        Lihat Semua Katalog
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @forelse($catalog as $goat)
                        <div class="group bg-white/40 backdrop-blur-md hover:bg-white/90 rounded-[2.5rem] overflow-hidden border border-white/20 shadow-[0_8px_32px_0_rgba(166,124,82,0.03)] hover:shadow-[0_20px_50px_rgba(74,103,65,0.12)] hover:-translate-y-2 transition-all duration-500">
                            <div class="relative aspect-square overflow-hidden">
                                @if($goat->image)
                                    <img src="{{ Storage::url($goat->image) }}" alt="{{ $goat->name }}" width="800" height="800" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                @else
                                    <div class="w-full h-full bg-[#4a6741]/5 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-[#4a6741]/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute top-4 left-4 flex gap-2">
                                    <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                        {{ $goat->breed ?? 'Lokal' }}
                                    </span>
                                    @if($goat->sale_status === 'auction')
                                        <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                            Lelang
                                        </span>
                                    @endif
                                    @if($goat->sale_status === 'sold')
                                        <span class="bg-red-600 text-white px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                            Terjual
                                        </span>
                                    @endif
                                </div>
                                @if($goat->sale_status === 'sold')
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[2px]">
                                        <span class="border-4 border-white text-white font-black text-2xl px-6 py-2 rotate-[-15deg] uppercase tracking-widest opacity-90">SOLD</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6 {{ $goat->sale_status === 'sold' ? 'opacity-75' : '' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-bold text-xl text-[#2d241e] mb-1">{{ $goat->name }}</h3>
                                        <p class="text-xs text-[#54483e] font-mono">{{ $goat->qr_code }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-[#54483e] uppercase font-bold tracking-tighter">Harga</p>
                                        <p class="text-[#4a6741] font-extrabold text-lg">Rp {{ number_format($goat->price, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 py-4 border-y border-[#a67c52]/10 my-4 text-center">
                                    <div>
                                        <p class="text-[10px] text-[#54483e] uppercase font-bold tracking-tighter">Bobot</p>
                                        <p class="font-bold text-sm">{{ $goat->current_weight ?? $goat->initial_weight ?? '-' }} kg</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-[#54483e] uppercase font-bold tracking-tighter">Tinggi</p>
                                        <p class="font-bold text-sm">{{ $goat->height ?? '-' }} cm</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-[#54483e] uppercase font-bold tracking-tighter">Umur</p>
                                        <p class="font-bold text-sm">{{ $goat->birth_date ? now()->diff($goat->birth_date)->y . ' Th' : '-' }}</p>
                                    </div>
                                </div>
                                @if($goat->sale_status === 'sold')
                                    <button disabled class="w-full text-center bg-gray-400 text-white py-3 rounded-xl font-bold cursor-not-allowed">
                                        Sudah Terjual
                                    </button>
                                @else
                                    <div class="flex gap-2">
                                        <a href="{{ route('catalog.show', $goat->qr_code) }}" class="flex-grow text-center bg-[#4a6741] text-white py-3 rounded-xl font-bold hover:bg-[#3a5233] transition-colors shadow-lg shadow-[#4a6741]/10">
                                            Lihat Detil
                                        </a>
                                        <form action="{{ route('cart.add') }}" method="POST" class="shrink-0">
                                            @csrf
                                            <input type="hidden" name="goat_id" value="{{ $goat->id }}">
                                            <button type="submit" class="p-3 bg-white border border-[#4a6741] text-[#4a6741] rounded-xl hover:bg-[#4a6741] hover:text-white transition-all shadow-lg shadow-[#4a6741]/5" title="Tambah ke Keranjang" aria-label="Tambah {{ $goat->name }} ke Keranjang">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center bg-[#fdfaf5] rounded-[2.5rem] border border-dashed border-[#a67c52]/20">
                            <p class="text-[#54483e] font-medium">Belum ada kambing yang dipajang di katalog saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-white rounded-[3rem] md:rounded-[5rem] mx-2 md:mx-6 shadow-sm border border-[#a67c52]/5 reveal">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-5xl font-extrabold text-[#2d241e] mb-4">Fitur Unggulan</h2>
                    <p class="text-[#54483e] max-w-xl mx-auto text-lg">Dilengkapi dengan teknologi terkini untuk membantu operasional kandang Anda setiap hari.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="group bg-white/40 backdrop-blur-md hover:bg-white/90 p-10 rounded-[2.5rem] border border-white/20 hover:border-[#4a6741]/50 hover:shadow-2xl hover:shadow-[#4a6741]/10 hover:-translate-y-2 transition-all duration-500">
                        <div class="w-16 h-16 bg-[#4a6741] text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-[#4a6741]/20 group-hover:rotate-6 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1V5a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1V5a1 1 0 011-1h2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-[#2d241e]">QR Identification</h3>
                        <p class="text-[#54483e] leading-relaxed">Identitas digital unik untuk setiap ekor kambing. Cukup scan untuk melihat riwayat lengkap tanpa ribet. Akurasi data terjamin.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group bg-white/40 backdrop-blur-md hover:bg-white/90 p-10 rounded-[2.5rem] border border-white/20 hover:border-[#4a6741]/50 hover:shadow-2xl hover:shadow-[#4a6741]/10 hover:-translate-y-2 transition-all duration-500">
                        <div class="w-16 h-16 bg-[#4a6741] text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-[#4a6741]/20 group-hover:rotate-6 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-[#2d241e]">Monitoring Kesehatan</h3>
                        <p class="text-[#54483e] leading-relaxed">Catat vaksinasi, riwayat medis, dan perkembangan berat badan secara real-time dengan grafik yang intuitif dan mendalam.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group bg-white/40 backdrop-blur-md hover:bg-white/90 p-10 rounded-[2.5rem] border border-white/20 hover:border-[#4a6741]/50 hover:shadow-2xl hover:shadow-[#4a6741]/10 hover:-translate-y-2 transition-all duration-500">
                        <div class="w-16 h-16 bg-[#4a6741] text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-[#4a6741]/20 group-hover:rotate-6 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-[#2d241e]">IoT Integration</h3>
                        <p class="text-[#54483e] leading-relaxed">Pantau kondisi lingkungan kandang secara otomatis. Notifikasi instan jika kondisi tidak ideal bagi ternak Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-24 overflow-hidden reveal stats-section">
            <div class="container mx-auto px-4">
                <div class="bg-[#2d241e] rounded-[3rem] p-12 md:p-20 text-center relative shadow-2xl">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative z-10">
                        <div class="reveal delay-100">
                            <div class="text-5xl md:text-7xl font-extrabold text-[#d0ab84] mb-2 tracking-tight">
                                <span class="stat-value" data-target="100">0</span>%
                            </div>
                            <div class="text-sm md:text-base font-bold text-white/75 uppercase tracking-widest">Digitalized Records</div>
                        </div>
                        <div class="reveal delay-200">
                            <div class="text-5xl md:text-7xl font-extrabold text-[#d0ab84] mb-2 tracking-tight">
                                <span class="stat-value" data-target="24">0</span>/7
                            </div>
                            <div class="text-sm md:text-base font-bold text-white/75 uppercase tracking-widest">Monitoring</div>
                        </div>
                        <div class="reveal delay-300">
                            <div class="text-5xl md:text-7xl font-extrabold text-[#d0ab84] mb-2 tracking-tight flex flex-col items-center justify-center">
                                <div><span class="stat-value" data-target="98">0</span>%</div>
                            </div>
                            <div class="text-sm md:text-base font-bold text-white/75 uppercase tracking-widest">AI Accuracy</div>
                            <!-- Mini interactive SVG chart -->
                            <svg class="w-24 h-8 mx-auto mt-3 text-[#d0ab84]" viewBox="0 0 100 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path id="graph-path" d="M5 25 C 20 25, 30 15, 50 12 C 70 9, 80 5, 95 3" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                                <path d="M5 25 C 20 25, 30 15, 50 12 C 70 9, 80 5, 95 3 L 95 30 L 5 30 Z" fill="url(#graph-grad)" opacity="0.15" />
                                <defs>
                                    <linearGradient id="graph-grad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="currentColor" />
                                        <stop offset="100%" stop-color="transparent" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <!-- Decorative background grid/dots pattern -->
                    <div class="absolute inset-0 bg-grid opacity-5 pointer-events-none rounded-[3rem]"></div>
                </div>
            </div>
        </section>

        <!-- Mission Section -->
        <section id="about" class="py-24 bg-white rounded-[3rem] md:rounded-[5rem] mx-2 md:mx-6 shadow-sm border border-[#a67c52]/5 reveal">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl md:text-5xl font-extrabold text-[#2d241e] mb-10">Misi Kami</h2>
                <p class="text-lg md:text-2xl text-[#54483e] max-w-4xl mx-auto leading-relaxed">
                    "Qandang lahir dari semangat untuk memajukan peternakan lokal melalui teknologi. Kami percaya bahwa dengan data yang tepat, setiap peternak bisa mengambil keputusan yang lebih baik, mengurangi risiko, dan meningkatkan profitabilitas."
                </p>
                <div class="mt-12 flex justify-center">
                    <div class="h-1 w-24 bg-[#4a6741] rounded-full"></div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-24 reveal">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-5xl font-extrabold text-[#2d241e] mb-4">Hubungi Kami</h2>
                    <p class="text-[#54483e] text-lg">Punya pertanyaan atau butuh bantuan instalasi?</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <div class="bg-white/60 backdrop-blur-md hover:bg-white/90 p-8 rounded-3xl border border-white/20 hover:border-[#4a6741]/50 shadow-lg hover:shadow-xl transition-all duration-500 flex items-center gap-6 group hover:-translate-y-1">
                        <div class="w-16 h-16 bg-[#4a6741]/10 text-[#4a6741] rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-[#54483e] uppercase tracking-wider mb-1">WhatsApp</div>
                            <div class="text-xl font-extrabold text-[#2d241e] hover:text-[#4a6741] transition-colors">+62 822-4499-4491</div>
                        </div>
                    </div>

                    <div class="bg-white/60 backdrop-blur-md hover:bg-white/90 p-8 rounded-3xl border border-white/20 hover:border-[#4a6741]/50 shadow-lg hover:shadow-xl transition-all duration-500 flex items-center gap-6 group hover:-translate-y-1">
                        <div class="w-16 h-16 bg-[#4a6741]/10 text-[#4a6741] rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-[#54483e] uppercase tracking-wider mb-1">Email Support</div>
                            <div class="text-xl font-extrabold text-[#2d241e] hover:text-[#4a6741] transition-colors">halo@qandang.com</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-24 px-4">
            <div class="container mx-auto max-w-6xl">
                <div class="bg-[#4a6741] rounded-[3rem] p-12 md:p-24 text-center text-white relative overflow-hidden">
                    <h2 class="text-4xl md:text-6xl font-extrabold mb-8 relative z-10 leading-tight">Siap Menjadi<br>Peternak Digital?</h2>
                    <p class="text-white/80 text-lg md:text-xl mb-12 max-w-xl mx-auto relative z-10">Gabung bersama ratusan peternak lainnya yang telah memodernisasi kandang mereka.</p>
                    <a href="/get-started" class="inline-block bg-white text-[#4a6741] px-12 py-5 rounded-2xl font-extrabold text-xl shadow-2xl hover:bg-[#fdfaf5] transition-all transform hover:-translate-y-1 relative z-10">
                        Gunakan Qandang Sekarang
                    </a>

                    <!-- Decorative Elements -->
                    <div class="absolute -top-24 -left-24 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-black/10 rounded-full blur-3xl"></div>
                </div>
            </div>
        </section>
    </main>

    <footer class="py-12 border-t border-[#a67c52]/10 bg-white">
        <div class="container mx-auto px-4 text-center">
            <div class="flex flex-col items-center gap-6">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.webp') }}" alt="Qandang Logo" width="450" height="450" class="h-10 w-auto" loading="lazy" decoding="async">
                    <span class="font-extrabold text-xl tracking-tight text-[#4a6741]">QANDANG</span>
                </a>
                <p class="text-[#54483e] max-w-md mx-auto">Platform pemantauan ternak cerdas untuk efisiensi dan profitabilitas maksimal.</p>
                <div class="w-full max-w-xs h-px bg-[#a67c52]/10"></div>
                <div class="text-[#54483e] text-sm">
                    Dev by <span class="font-bold text-[#4a6741]">NM Co.</span> © 2026 Qandang. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Start preloader animation immediately
        setTimeout(() => {
            const bar = document.getElementById('preloader-bar');
            if (bar) bar.style.width = '65%';
        }, 50);

        window.addEventListener('load', () => {
            const bar = document.getElementById('preloader-bar');
            const preloader = document.getElementById('preloader');
            if (bar && preloader) {
                bar.style.width = '100%';
                setTimeout(() => {
                    preloader.style.opacity = '0';
                    preloader.style.pointerEvents = 'none';
                    setTimeout(() => {
                        preloader.remove();
                    }, 700);
                }, 300);
            }
        });

        // Hero Tabs switcher
        function switchHeroTab(tab) {
            const promoBtn = document.getElementById('tab-promo-btn');
            const scanBtn = document.getElementById('tab-scan-btn');
            const promoPanel = document.getElementById('panel-promo');
            const scanPanel = document.getElementById('panel-scan');

            if (tab === 'promo') {
                promoBtn.className = 'flex-1 py-3 px-4 rounded-xl text-sm font-extrabold transition-all duration-300 bg-white shadow-sm text-[#4a6741]';
                scanBtn.className = 'flex-1 py-3 px-4 rounded-xl text-sm font-extrabold transition-all duration-300 text-slate-600 hover:text-[#4a6741]';
                promoPanel.classList.remove('hidden');
                scanPanel.classList.add('hidden');
            } else {
                scanBtn.className = 'flex-1 py-3 px-4 rounded-xl text-sm font-extrabold transition-all duration-300 bg-white shadow-sm text-[#4a6741]';
                promoBtn.className = 'flex-1 py-3 px-4 rounded-xl text-sm font-extrabold transition-all duration-300 text-slate-600 hover:text-[#4a6741]';
                scanPanel.classList.remove('hidden');
                promoPanel.classList.add('hidden');
            }
        }

        // Mock scanner action
        function triggerMockScan() {
            const laser = document.getElementById('scan-laser');
            const qr = document.getElementById('mock-qr');
            const successIcon = document.getElementById('scan-success-icon');
            const instruction = document.getElementById('scan-instruction');
            const result = document.getElementById('scan-result');

            laser.style.animationDuration = '1.2s';
            qr.classList.add('scale-95');

            setTimeout(() => {
                successIcon.classList.remove('opacity-0', 'scale-50');
                successIcon.classList.add('opacity-100', 'scale-100');
                laser.classList.add('hidden');

                setTimeout(() => {
                    successIcon.classList.add('opacity-0');
                    instruction.classList.add('hidden');
                    result.classList.remove('hidden');
                    result.style.opacity = '0';
                    setTimeout(() => {
                        result.style.transition = 'opacity 0.5s ease-out';
                        result.style.opacity = '1';
                    }, 50);
                }, 800);
            }, 1800);
        }

        function resetMockScan() {
            const laser = document.getElementById('scan-laser');
            const qr = document.getElementById('mock-qr');
            const successIcon = document.getElementById('scan-success-icon');
            const instruction = document.getElementById('scan-instruction');
            const result = document.getElementById('scan-result');

            result.classList.add('hidden');
            instruction.classList.remove('hidden');
            successIcon.className = 'absolute inset-0 bg-[#4a6741]/90 flex items-center justify-center opacity-0 scale-50 transition-all duration-500 z-30';
            qr.classList.remove('scale-95');
            laser.classList.remove('hidden');
            laser.style.animationDuration = '3s';
        }

        // Counter Stats Animation
        function animateStats() {
            const stats = document.querySelectorAll('.stat-value');
            stats.forEach(stat => {
                const target = parseInt(stat.getAttribute('data-target'));
                const duration = 2000; // ms
                const startTime = performance.now();
                
                function updateNumber(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // Ease out cubic
                    const easeProgress = 1 - Math.pow(1 - progress, 3);
                    const value = Math.floor(easeProgress * target);
                    stat.textContent = value;
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateNumber);
                    } else {
                        stat.textContent = target;
                    }
                }
                requestAnimationFrame(updateNumber);
            });
        }

        // --- NEW WIDGET FUNCTIONS ---
        // Tab switcher inside widget
        function switchChatTab(tab) {
            const aiTabBtn = document.getElementById('tab-ai-chat-btn');
            const csTabBtn = document.getElementById('tab-cs-wa-btn');
            const aiPanel = document.getElementById('panel-ai-chat');
            const csPanel = document.getElementById('panel-cs-wa');

            if (tab === 'ai') {
                aiTabBtn.className = 'flex-1 py-2 text-center text-xs font-extrabold transition-all duration-300 border-b-2 border-[#4a6741] text-[#4a6741]';
                csTabBtn.className = 'flex-1 py-2 text-center text-xs font-extrabold transition-all duration-300 border-b-2 border-transparent text-slate-500 hover:text-[#4a6741]';
                aiPanel.classList.remove('hidden');
                csPanel.classList.add('hidden');
            } else {
                csTabBtn.className = 'flex-1 py-2 text-center text-xs font-extrabold transition-all duration-300 border-b-2 border-[#4a6741] text-[#4a6741]';
                aiTabBtn.className = 'flex-1 py-2 text-center text-xs font-extrabold transition-all duration-300 border-b-2 border-transparent text-slate-500 hover:text-[#4a6741]';
                csPanel.classList.remove('hidden');
                aiPanel.classList.add('hidden');
            }
        }

        // WhatsApp CS sender
        function sendWaMessage() {
            const name = document.getElementById('wa-cs-name').value.trim();
            const msg = document.getElementById('wa-cs-msg').value.trim();
            if (!msg) {
                alert('Silakan tulis pesan Anda terlebih dahulu.');
                return;
            }
            const phone = '6282244994491';
            const template = `Halo CS Qandang, saya ${name || 'Pengunjung'}.\n\nPertanyaan:\n${msg}`;
            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(template)}`, '_blank');
        }

        // Suggested AI Prompt click
        function sendSuggestedPrompt(prompt) {
            const input = document.getElementById('ai-chat-input');
            if (input) {
                input.value = prompt;
                submitAiQuery();
            }
        }

        function handleAiInputKey(e) {
            if (e.key === 'Enter') {
                submitAiQuery();
            }
        }

        const aiAnswers = {
            "prediksi": "AI Qandang memprediksi pertumbuhan berat badan kambing 30 hari ke depan dengan menganalisis histori timbangan, jenis ras, dan asupan pakan. Akurasi mencapai 98%!",
            "qr": "Cetak QR Code bisa dilakukan lewat panel admin di menu 'Data Kambing'. Gunakan ear-tag plastik anti-air dengan ukuran minimal 3x3 cm agar awet dan mudah dipindai di lapangan.",
            "iot": "Sistem IoT memantau suhu kandang, kelembapan udara, dan kadar gas amonia secara real-time via broker MQTT, serta otomatis menyalakan kipas blower jika amonia terlalu tinggi.",
            "harga": "Kambing di Qandang bisa dibeli langsung lewat menu Katalog. Tambahkan ke keranjang, checkout, dan Admin kami akan menghubungi Anda untuk konfirmasi pengiriman manual."
        };

        function submitAiQuery() {
            const input = document.getElementById('ai-chat-input');
            const messages = document.getElementById('chat-messages');
            const query = input.value.trim();
            if (!query) return;

            input.value = '';

            const userMsgHtml = `
                <div class="flex gap-2 justify-end">
                    <div class="bg-[#4a6741] text-white p-3 rounded-2xl rounded-tr-none font-medium leading-relaxed max-w-[85%]">
                        ${query}
                    </div>
                </div>
            `;
            messages.insertAdjacentHTML('beforeend', userMsgHtml);
            messages.scrollTop = messages.scrollHeight;

            const typingId = 'typing-' + Date.now();
            const typingHtml = `
                <div id="${typingId}" class="flex gap-2 animate-pulse">
                    <div class="w-8 h-6 bg-[#4a6741]/10 rounded-lg flex items-center justify-center text-[10px] shrink-0 font-bold text-[#4a6741]">Mbek</div>
                    <div class="bg-[#fdfaf5] border border-[#a67c52]/10 p-3 rounded-2xl rounded-tl-none text-[#2d241e]/50 font-bold max-w-[85%]">
                        Mengetik...
                    </div>
                </div>
            `;
            messages.insertAdjacentHTML('beforeend', typingHtml);
            messages.scrollTop = messages.scrollHeight;

            let response = "Pertanyaan menarik! Silakan hubungi Customer Service kami di tab sebelah untuk bantuan langsung via WhatsApp atau kunjungi menu Dokumentasi di panel admin.";
            const cleanQuery = query.toLowerCase();
            for (const key in aiAnswers) {
                if (cleanQuery.includes(key)) {
                    response = aiAnswers[key];
                    break;
                }
            }

            setTimeout(() => {
                const typingEl = document.getElementById(typingId);
                if (typingEl) {
                    typingEl.remove();
                }
                const aiMsgHtml = `
                    <div class="flex gap-2">
                        <div class="w-8 h-6 bg-[#4a6741]/10 rounded-lg flex items-center justify-center text-[10px] shrink-0 font-bold text-[#4a6741]">Mbek</div>
                        <div class="bg-[#fdfaf5] border border-[#a67c52]/10 p-3 rounded-2xl rounded-tl-none text-[#2d241e] font-medium leading-relaxed max-w-[85%] animate-fade-in">
                            ${response}
                        </div>
                    </div>
                `;
                messages.insertAdjacentHTML('beforeend', aiMsgHtml);
                messages.scrollTop = messages.scrollHeight;
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Mobile Menu
            const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
            const mobileMenu = document.querySelector('[data-mobile-menu]');

            if (mobileMenuToggle && mobileMenu) {
                const closeMenu = () => {
                    mobileMenu.hidden = true;
                    mobileMenuToggle.setAttribute('aria-expanded', 'false');
                };

                const openMenu = () => {
                    mobileMenu.hidden = false;
                    mobileMenuToggle.setAttribute('aria-expanded', 'true');
                };

                mobileMenuToggle.addEventListener('click', () => {
                    if (mobileMenu.hidden) {
                        openMenu();
                    } else {
                        closeMenu();
                    }
                });

                mobileMenu.querySelectorAll('[data-mobile-menu-close]').forEach((link) => {
                    link.addEventListener('click', closeMenu);
                });
            }

            // Dismissible banners
            document.querySelectorAll('[data-dismissible-banner]').forEach((banner) => {
                const dismissButton = banner.querySelector('[data-dismissible-banner-close]');
                if (dismissButton) {
                    dismissButton.addEventListener('click', () => {
                        banner.hidden = true;
                    });
                }
            });

            // Scroll Reveal Observer
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        
                        // If current section is stats, start the counters
                        if (entry.target.classList.contains('stats-section')) {
                            animateStats();
                        }
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => {
                observer.observe(el);
            });

            // Toggle Chat Panel
            const chatBubble = document.getElementById('chat-bubble-btn');
            const chatPanel = document.getElementById('chat-panel');
            const bubbleIcon = document.getElementById('chat-bubble-icon');
            const closeIcon = document.getElementById('chat-close-icon');

            if (chatBubble && chatPanel) {
                chatBubble.addEventListener('click', () => {
                    if (chatPanel.classList.contains('hidden')) {
                        chatPanel.classList.remove('hidden');
                        setTimeout(() => {
                            chatPanel.classList.remove('scale-95', 'opacity-0');
                            chatPanel.classList.add('scale-100', 'opacity-100');
                        }, 50);
                        bubbleIcon.classList.add('hidden');
                        closeIcon.classList.remove('hidden');
                    } else {
                        chatPanel.classList.remove('scale-100', 'opacity-100');
                        chatPanel.classList.add('scale-95', 'opacity-0');
                        setTimeout(() => {
                            chatPanel.classList.add('hidden');
                        }, 300);
                        closeIcon.classList.add('hidden');
                        bubbleIcon.classList.remove('hidden');
                    }
                });
            }

            // Back to Top & Scroll Progress
            const backToTopBtn = document.getElementById('back-to-top-btn');
            const progressCircle = document.getElementById('scroll-progress-circle');

            if (backToTopBtn && progressCircle) {
                window.addEventListener('scroll', () => {
                    const scrollTop = window.scrollY || document.documentElement.scrollTop;
                    const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    
                    if (scrollHeight > 0) {
                        const progress = (scrollTop / scrollHeight) * 100;
                        progressCircle.style.strokeDashoffset = 100 - progress;
                    }

                    if (scrollTop > 350) {
                        backToTopBtn.classList.remove('opacity-0', 'invisible', 'translate-y-4');
                        backToTopBtn.classList.add('opacity-100', 'visible', 'translate-y-0');
                    } else {
                        backToTopBtn.classList.remove('opacity-100', 'visible', 'translate-y-0');
                        backToTopBtn.classList.add('opacity-0', 'invisible', 'translate-y-4');
                    }
                });

                backToTopBtn.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });
    </script>

    <!-- Qandang Interactive Chat Assistant Widget -->
    <div class="fixed bottom-6 left-6 z-50">
        <!-- Floating Bubble Button -->
        <button id="chat-bubble-btn" class="w-16 h-16 bg-[#4a6741] text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-transform active:scale-95 border-2 border-white/20 glow-btn" aria-label="Tanya AI & CS Qandang">
            <!-- Chat SVG Icon -->
            <svg id="chat-bubble-icon" class="w-8 h-8 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <!-- Close SVG Icon (hidden initially) -->
            <svg id="chat-close-icon" class="w-8 h-8 hidden transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Chat Panel Window -->
        <div id="chat-panel" class="absolute bottom-20 left-0 w-[340px] sm:w-[380px] bg-white/90 backdrop-blur-xl border border-[#a67c52]/10 rounded-3xl shadow-2xl overflow-hidden hidden transition-all duration-300 scale-95 opacity-0 origin-bottom-left">
            <!-- Header -->
            <div class="bg-[#4a6741] p-5 text-white flex items-center gap-3">
                <div class="relative w-10 h-10 bg-white rounded-xl flex items-center justify-center p-1.5 shrink-0 shadow-md">
                    <img src="{{ asset('images/logo.webp') }}" alt="Qandang Logo" class="w-full h-full object-contain">
                    <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-[#4a6741] rounded-full"></span>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm tracking-wide">Asisten Pintar Qandang</h3>
                    <p class="text-[10px] text-white/70 font-semibold uppercase tracking-wider flex items-center gap-1">
                        AI & CS Online
                    </p>
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="flex border-b border-[#a67c52]/10 bg-slate-50/80 p-1">
                <button id="tab-ai-chat-btn" class="flex-1 py-2 text-center text-xs font-extrabold transition-all duration-300 border-b-2 border-transparent text-slate-500 hover:text-[#4a6741]" onclick="switchChatTab('ai')">
                    🐐 Tanya Mbek
                </button>
                <button id="tab-cs-wa-btn" class="flex-1 py-2 text-center text-xs font-extrabold transition-all duration-300 border-b-2 border-[#4a6741] text-[#4a6741]" onclick="switchChatTab('wa')">
                    💬 CS WhatsApp
                </button>
            </div>

            <!-- Panel Content -->
            <div class="p-5 min-h-[320px] max-h-[380px] flex flex-col justify-between">
                
                <!-- Tab AI Panel -->
                <div id="panel-ai-chat" class="hidden flex-col flex-1">
                    <!-- Message Log -->
                    <div id="chat-messages" class="flex-1 overflow-y-auto max-h-[160px] space-y-3 pr-1 text-xs mb-3 scroll-smooth">
                        <!-- Welcoming message -->
                        <div class="flex gap-2">
                            <div class="w-8 h-6 bg-[#4a6741]/10 rounded-lg flex items-center justify-center text-[10px] shrink-0 font-bold text-[#4a6741]">Mbek</div>
                            <div class="bg-[#fdfaf5] border border-[#a67c52]/10 p-3 rounded-2xl rounded-tl-none text-[#2d241e] font-medium leading-relaxed max-w-[85%]">
                                Halo! Saya Mbek, asisten virtual kandang Qandang. Silakan klik topik di bawah atau tanya apa saja terkait kambing Anda.
                            </div>
                        </div>
                    </div>

                    <!-- Suggested Prompts -->
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <button onclick="sendSuggestedPrompt('Bagaimana prediksi berat?')" class="text-[9px] font-bold text-[#4a6741] bg-[#4a6741]/5 border border-[#4a6741]/20 hover:bg-[#4a6741]/10 px-2 py-0.5 rounded-full transition-all">📈 Prediksi Berat</button>
                        <button onclick="sendSuggestedPrompt('Bagaimana cara cetak QR tag?')" class="text-[9px] font-bold text-[#4a6741] bg-[#4a6741]/5 border border-[#4a6741]/20 hover:bg-[#4a6741]/10 px-2 py-0.5 rounded-full transition-all">🔍 Cetak QR</button>
                        <button onclick="sendSuggestedPrompt('IoT memantau sensor apa saja?')" class="text-[9px] font-bold text-[#4a6741] bg-[#4a6741]/5 border border-[#4a6741]/20 hover:bg-[#4a6741]/10 px-2 py-0.5 rounded-full transition-all">🌡️ Sensor IoT</button>
                    </div>

                    <!-- AI Input Form -->
                    <div class="flex gap-2 border-t border-[#a67c52]/5 pt-3">
                        <input id="ai-chat-input" type="text" placeholder="Tanya Mbek..." class="flex-1 bg-slate-50 border border-[#a67c52]/10 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-[#4a6741] transition-colors" onkeydown="handleAiInputKey(event)">
                        <button onclick="submitAiQuery()" class="p-2.5 bg-[#4a6741] text-white rounded-xl hover:bg-[#3a5233] transition-colors shrink-0">
                            <svg class="w-4 h-4 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Tab CS WA Panel -->
                <div id="panel-cs-wa" class="flex flex-col flex-1 justify-between">
                    <div class="space-y-4">
                        <p class="text-xs text-[#54483e] font-medium leading-relaxed">
                            Butuh bantuan langsung? Isi pesan di bawah untuk dikirim langsung via WhatsApp CS kami.
                        </p>
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider font-extrabold text-[#54483e] mb-1">Nama Anda</label>
                            <input id="wa-cs-name" type="text" placeholder="Nama lengkap..." class="w-full bg-slate-50 border border-[#a67c52]/10 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-[#4a6741] transition-colors">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider font-extrabold text-[#54483e] mb-1">Pesan Anda</label>
                            <textarea id="wa-cs-msg" placeholder="Tulis pertanyaan Anda..." rows="3" class="w-full bg-slate-50 border border-[#a67c52]/10 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-[#4a6741] transition-colors resize-none"></textarea>
                        </div>
                    </div>

                    <button onclick="sendWaMessage()" class="w-full bg-[#4a6741] hover:bg-[#3a5233] text-white py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 mt-4 shadow-lg shadow-[#4a6741]/10">
                        <!-- WA Icon SVG -->
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.37 5.054L2 22l5.075-1.33a9.92 9.92 0 004.936 1.314c5.507 0 9.99-4.479 9.99-9.988 0-2.668-1.039-5.176-2.927-7.067C17.186 3.039 14.679 2 12.012 2zm6.59 13.918c-.288.812-1.42 1.485-1.956 1.55-.494.06-1.13.085-1.815-.15-.68-.234-1.615-.658-2.686-1.127-4.526-1.986-7.46-6.602-7.687-6.905-.226-.303-1.803-2.4-1.803-4.58 0-2.181 1.13-3.255 1.534-3.707.404-.453.88-.567 1.171-.567h.834c.266 0 .498.01.734.58.267.643.914 2.23.993 2.392.079.163.132.353.023.567-.109.213-.163.353-.325.543-.162.19-.34.425-.486.57-.162.16-.33.33-.142.658.188.324.835 1.373 1.787 2.22 1.229 1.096 2.266 1.436 2.587 1.597.32.161.508.134.698-.083.19-.217.81-.94 1.026-1.264.217-.324.433-.27.734-.162.3.109 1.9.897 2.226 1.06.326.163.543.245.623.38.08.136.08.786-.208 1.598z"/>
                        </svg>
                        Kirim via WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Back To Top Button with Circular Scroll Progress -->
    <button id="back-to-top-btn" class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-white hover:bg-slate-50 text-[#4a6741] rounded-full flex items-center justify-center shadow-2xl transition-all duration-300 opacity-0 invisible translate-y-4 hover:scale-110 active:scale-95" aria-label="Kembali ke Atas">
        <!-- SVG Progress Indicator -->
        <svg class="absolute inset-0 w-full h-full -rotate-90" viewBox="0 0 36 36">
            <path class="text-slate-100" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            <path id="scroll-progress-circle" class="text-[#4a6741] transition-all duration-75" stroke="currentColor" stroke-width="3" stroke-dasharray="100, 100" stroke-dashoffset="100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
        </svg>
        <!-- Arrow Up Icon -->
        <svg class="w-6 h-6 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>
</body>
</html>
