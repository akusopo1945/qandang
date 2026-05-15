<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Qandang - Smart Livestock Monitoring</title>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Qandang - Smart Livestock Monitoring">
    <meta property="og:description" content="Solusi Smart Farming modern untuk monitoring ternak kambing digital menggunakan QR Code.">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        .bg-pattern {
            background-color: #fdfaf5;
            background-image: radial-gradient(#4a6741 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            background-opacity: 0.05;
        }

        .hero-gradient {
            background: radial-gradient(circle at top right, rgba(74, 103, 65, 0.08) 0%, transparent 40%),
                        radial-gradient(circle at bottom left, rgba(166, 124, 82, 0.05) 0%, transparent 40%);
        }
    </style>
</head>
<body class="antialiased text-[#2d241e] bg-[#fdfaf5] font-sans selection:bg-[#4a6741] selection:text-white">

    <!-- Header -->
    <header x-data="{ scrolled: false, mobileMenuOpen: false }" 
            @scroll.window="scrolled = (window.pageYOffset > 20)"
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
            :class="scrolled ? 'py-3' : 'py-6'">

        <div class="container mx-auto px-4 md:px-6">
            <nav class="relative flex items-center justify-between bg-white/80 backdrop-blur-xl border border-[#a67c52]/10 shadow-lg px-6 py-3 rounded-2xl md:rounded-[2rem] transition-all duration-500"
                 :class="scrolled ? 'shadow-xl border-[#a67c52]/20' : ''">

                <!-- Logo -->
                <a href="/" class="flex items-center gap-2 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Qandang Logo" class="h-10 md:h-12 w-auto transition-transform duration-500 group-hover:scale-105">
                    <span class="hidden sm:block font-extrabold text-xl tracking-tight text-[#4a6741]">QANDANG</span>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden lg:flex items-center gap-8">
                    <a href="#features" class="text-sm font-bold uppercase tracking-wider hover:text-[#4a6741] transition-colors">Fitur</a>
                    <a href="#about" class="text-sm font-bold uppercase tracking-wider hover:text-[#4a6741] transition-colors">Misi</a>
                    <a href="#contact" class="text-sm font-bold uppercase tracking-wider hover:text-[#4a6741] transition-colors">Kontak</a>
                    <a href="/get-started" class="bg-[#4a6741] hover:bg-[#3a5233] text-white px-6 py-2.5 rounded-full font-bold text-sm uppercase tracking-wide shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                        Mulai Sekarang
                    </a>
                </div>

                <!-- Mobile Menu Toggle -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-[#2d241e]">
                    <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18" />
                    </svg>
                </button>
            </nav>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="lg:hidden absolute top-full left-0 right-0 mt-4 px-4">
            <div class="bg-white border border-[#a67c52]/10 rounded-3xl shadow-2xl p-6 flex flex-col gap-4">
                <a @click="mobileMenuOpen = false" href="#features" class="text-lg font-bold p-2 hover:text-[#4a6741]">Fitur</a>
                <a @click="mobileMenuOpen = false" href="#about" class="text-lg font-bold p-2 hover:text-[#4a6741]">Misi</a>
                <a @click="mobileMenuOpen = false" href="#contact" class="text-lg font-bold p-2 hover:text-[#4a6741]">Kontak</a>
                <hr class="border-[#a67c52]/10">
                <a href="/get-started" class="bg-[#4a6741] text-white text-center py-4 rounded-2xl font-bold text-lg">
                    Mulai Sekarang
                </a>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden hero-gradient">
            <div class="container mx-auto px-4 text-center relative z-10">
                <div class="inline-flex items-center gap-2 bg-[#4a6741]/10 px-4 py-2 rounded-full mb-6 border border-[#4a6741]/20">
                    <span class="w-2 h-2 bg-[#4a6741] rounded-full animate-pulse"></span>
                    <span class="text-xs md:text-sm font-bold text-[#4a6741] uppercase tracking-widest">Smart Farming Solution</span>
                </div>

                <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-[#2d241e] leading-[1.1] mb-6 max-w-4xl mx-auto tracking-tight">
                    Urus Ternak Lebih Mudah dengan <span class="text-[#4a6741] relative">Digitalisasi<span class="absolute bottom-1 left-0 w-full h-3 bg-[#4a6741]/10 -z-10"></span></span>
                </h1>

                <p class="text-lg md:text-xl text-[#6b5e51] max-w-2xl mx-auto mb-10 leading-relaxed">
                    Monitor pertumbuhan, kesehatan, dan manajemen kandang dalam satu genggaman. Efisiensi maksimal untuk peternak modern.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/get-started" class="w-full sm:w-auto bg-[#4a6741] hover:bg-[#3a5233] text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-xl shadow-[#4a6741]/20 hover:shadow-2xl hover:shadow-[#4a6741]/30 transition-all transform hover:-translate-y-1">
                        Gunakan Qandang
                    </a>
                    <a href="#features" class="w-full sm:w-auto flex items-center justify-center gap-2 px-10 py-4 font-bold text-lg hover:text-[#4a6741] transition-colors">
                        Lihat Fitur
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Background Elements -->
            <div class="absolute top-1/4 -left-20 w-64 h-64 bg-[#4a6741]/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-[#a67c52]/5 rounded-full blur-3xl"></div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-white rounded-[3rem] md:rounded-[5rem] mx-2 md:mx-6 shadow-sm border border-[#a67c52]/5">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-5xl font-extrabold text-[#2d241e] mb-4">Fitur Unggulan</h2>
                    <p class="text-[#6b5e51] max-w-xl mx-auto text-lg">Dilengkapi dengan teknologi terkini untuk membantu operasional kandang Anda setiap hari.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="group bg-[#fdfaf5] p-10 rounded-[2.5rem] border border-[#a67c52]/10 hover:border-[#4a6741] hover:bg-white hover:shadow-2xl hover:shadow-[#4a6741]/5 transition-all duration-500">
                        <div class="w-16 h-16 bg-[#4a6741] text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-[#4a6741]/20 group-hover:rotate-6 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1V5a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1V5a1 1 0 011-1h2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">QR Identification</h3>
                        <p class="text-[#6b5e51] leading-relaxed">Identitas digital unik untuk setiap ekor kambing. Cukup scan untuk melihat riwayat lengkap tanpa ribet. Akurasi data terjamin.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group bg-[#fdfaf5] p-10 rounded-[2.5rem] border border-[#a67c52]/10 hover:border-[#4a6741] hover:bg-white hover:shadow-2xl hover:shadow-[#4a6741]/5 transition-all duration-500">
                        <div class="w-16 h-16 bg-[#4a6741] text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-[#4a6741]/20 group-hover:rotate-6 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">Monitoring Kesehatan</h3>
                        <p class="text-[#6b5e51] leading-relaxed">Catat vaksinasi, riwayat medis, dan perkembangan berat badan secara real-time dengan grafik yang intuitif dan mendalam.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group bg-[#fdfaf5] p-10 rounded-[2.5rem] border border-[#a67c52]/10 hover:border-[#4a6741] hover:bg-white hover:shadow-2xl hover:shadow-[#4a6741]/5 transition-all duration-500">
                        <div class="w-16 h-16 bg-[#4a6741] text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-[#4a6741]/20 group-hover:rotate-6 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">IoT Integration</h3>
                        <p class="text-[#6b5e51] leading-relaxed">Pantau kondisi lingkungan kandang secara otomatis. Notifikasi instan jika kondisi tidak ideal bagi ternak Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-24 overflow-hidden">
            <div class="container mx-auto px-4">
                <div class="bg-[#2d241e] rounded-[3rem] p-12 md:p-20 text-center relative">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative z-10">
                        <div>
                            <div class="text-5xl md:text-7xl font-extrabold text-[#a67c52] mb-2 tracking-tight">100%</div>
                            <div class="text-sm md:text-base font-bold text-white/60 uppercase tracking-widest">Digitalized Records</div>
                        </div>
                        <div>
                            <div class="text-5xl md:text-7xl font-extrabold text-[#a67c52] mb-2 tracking-tight">24/7</div>
                            <div class="text-sm md:text-base font-bold text-white/60 uppercase tracking-widest">Monitoring</div>
                        </div>
                        <div>
                            <div class="text-5xl md:text-7xl font-extrabold text-[#a67c52] mb-2 tracking-tight">AI</div>
                            <div class="text-sm md:text-base font-bold text-white/60 uppercase tracking-widest">Growth Prediction</div>
                        </div>
                    </div>
                    <!-- Decorative dots -->
                    <div class="absolute top-0 left-0 w-full h-full bg-pattern opacity-10 pointer-events-none"></div>
                </div>
            </div>
        </section>

        <!-- Mission Section -->
        <section id="about" class="py-24 bg-white rounded-[3rem] md:rounded-[5rem] mx-2 md:mx-6 shadow-sm border border-[#a67c52]/5">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl md:text-5xl font-extrabold text-[#2d241e] mb-10">Misi Kami</h2>
                <p class="text-lg md:text-2xl text-[#6b5e51] max-w-4xl mx-auto leading-relaxed">
                    "Qandang lahir dari semangat untuk memajukan peternakan lokal melalui teknologi. Kami percaya bahwa dengan data yang tepat, setiap peternak bisa mengambil keputusan yang lebih baik, mengurangi risiko, dan meningkatkan profitabilitas."
                </p>
                <div class="mt-12 flex justify-center">
                    <div class="h-1 w-24 bg-[#4a6741] rounded-full"></div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-24">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-5xl font-extrabold text-[#2d241e] mb-4">Hubungi Kami</h2>
                    <p class="text-[#6b5e51] text-lg">Punya pertanyaan atau butuh bantuan instalasi?</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <div class="bg-white p-8 rounded-3xl border border-[#a67c52]/10 shadow-lg flex items-center gap-6">
                        <div class="w-16 h-16 bg-[#4a6741]/10 text-[#4a6741] rounded-2xl flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-[#6b5e51] uppercase tracking-wider mb-1">WhatsApp</div>
                            <div class="text-xl font-extrabold text-[#2d241e]">+62 812-3456-7890</div>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-[#a67c52]/10 shadow-lg flex items-center gap-6">
                        <div class="w-16 h-16 bg-[#4a6741]/10 text-[#4a6741] rounded-2xl flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-[#6b5e51] uppercase tracking-wider mb-1">Email Support</div>
                            <div class="text-xl font-extrabold text-[#2d241e]">halo@qandang.com</div>
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
                    <img src="{{ asset('images/logo.png') }}" alt="Qandang Logo" class="h-10 w-auto">
                    <span class="font-extrabold text-xl tracking-tight text-[#4a6741]">QANDANG</span>
                </a>
                <p class="text-[#6b5e51] max-w-md mx-auto">Platform pemantauan ternak cerdas untuk efisiensi dan profitabilitas maksimal.</p>
                <div class="w-full max-w-xs h-px bg-[#a67c52]/10"></div>
                <div class="text-[#6b5e51] text-sm">
                    Dev by <span class="font-bold text-[#4a6741]">CakDoel & theGong</span> © 2026 Qandang. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <!-- Alpine.js for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>



</body>
</html>
