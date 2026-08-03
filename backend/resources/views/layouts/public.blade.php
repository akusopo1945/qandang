<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Qandang - Smart Livestock Monitoring')</title>
    <meta name="description" content="@yield('meta_description', 'Qandang adalah platform smart farming untuk monitoring ternak kambing digital dengan QR Code, katalog, wishlist, dan keranjang.')">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @yield('extra_meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        .bg-pattern {
            background-color: #fdfaf5;
            background-image: radial-gradient(#4a6741 0.5px, transparent 0.5px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="antialiased text-[#2d241e] bg-[#fdfaf5] font-sans selection:bg-[#4a6741] selection:text-white">
    <header class="fixed top-0 left-0 right-0 z-50 py-6">
        <div class="container mx-auto px-4 md:px-6">
            <nav class="relative flex items-center justify-between bg-white/80 backdrop-blur-xl border border-[#a67c52]/10 shadow-lg px-6 py-3 rounded-2xl">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.webp') }}" alt="Qandang Logo" width="450" height="450" class="h-10 w-auto" loading="eager" decoding="async">
                    <span class="font-extrabold text-xl tracking-tight text-[#4a6741]">QANDANG</span>
                </a>
                <div class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('catalog') }}" class="text-sm font-bold uppercase tracking-wider hover:text-[#4a6741]">Katalog</a>
                    <a href="/#features" class="text-sm font-bold uppercase tracking-wider hover:text-[#4a6741]">Fitur</a>
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
                    <a href="/get-started" class="bg-[#4a6741] text-white px-6 py-2.5 rounded-full font-bold text-sm uppercase">Mulai</a>
                </div>

                <!-- Mobile Header Icons -->
                <div class="flex items-center gap-2 lg:hidden">
                    <a href="{{ route('wishlist.index') }}" class="relative p-2 text-[#2d241e] hover:text-[#4a6741]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        @if($wishlist_count > 0)
                            <span class="absolute top-1 right-1 bg-red-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full">{{ $wishlist_count }}</span>
                        @endif
                    </a>
                    
                    <!-- Mobile Menu Toggle -->
                    <button type="button" data-mobile-menu-toggle aria-controls="public-mobile-menu" aria-expanded="false" class="p-2 text-[#2d241e]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </nav>
        </div>

        <!-- Mobile Menu -->
        <div id="public-mobile-menu"
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
                <a data-mobile-menu-close href="/#features" class="text-lg font-bold p-2 hover:text-[#4a6741]">Fitur</a>
                <hr class="border-[#a67c52]/10">
                <a href="/get-started" class="bg-[#4a6741] text-white text-center py-4 rounded-2xl font-bold text-lg">
                    Mulai Sekarang
                </a>
            </div>
        </div>
    </header>

    <main class="pt-32">
        @yield('content')
    </main>

    <footer class="py-12 border-t border-[#a67c52]/10 bg-white mt-24">
        <div class="container mx-auto px-4 text-center">
            <p class="text-[#6b5e51]">© 2026 Qandang. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
