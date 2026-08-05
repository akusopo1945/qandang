<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Filament\Pages\Auth\CustomLogin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->registration()
            ->profile()
            ->userMenuItems([
                \Filament\Navigation\MenuItem::make()
                    ->label('Manajemen User')
                    ->url('/admin/users')
                    ->icon('heroicon-o-users'),
            ])
            ->colors([
                'primary' => '#4a6741', // Hunter Green
                'secondary' => '#a67c52', // Terracotta Brown
                'gray' => Color::Stone,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'info' => Color::Sky,
            ])
            ->font('Outfit')
            ->sidebarCollapsibleOnDesktop()
            ->brandName('Qandang')
            ->favicon(asset('favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => '
                    <link rel="icon" type="image/x-icon" href="' . asset('favicon.ico') . '">
                    <link rel="icon" type="image/png" sizes="32x32" href="' . asset('images/logo.webp') . '">
                    <link rel="apple-touch-icon" href="' . asset('images/logo.webp') . '">
                    <meta property="og:title" content="Qandang - Smart Livestock Monitoring">
                    <meta property="og:description" content="Platform smart farming untuk monitoring ternak kambing digital menggunakan QR Code.">
                    <meta property="og:image" content="' . asset('images/og-image.jpg') . '">
                    <meta property="og:url" content="' . config('app.url') . '">
                    <meta property="og:type" content="website">
                    <meta name="twitter:card" content="summary_large_image">
                    <style>
                        /* Modern Bento Grid Cards with soft nature-inspired shadows */
                        .fi-wi-stats-overview-stat-card, .fi-card, .fi-ta-ctn {
                            border: 1px solid rgba(166, 124, 82, 0.08) !important;
                            box-shadow: 0 10px 30px -10px rgba(74, 103, 65, 0.04), 0 1px 3px rgba(166, 124, 82, 0.01) !important;
                            border-radius: 1.25rem !important;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            background-color: rgba(255, 255, 255, 0.8) !important;
                            backdrop-filter: blur(8px) !important;
                        }
                        .fi-wi-stats-overview-stat-card:hover, .fi-card:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 20px 40px -15px rgba(74, 103, 65, 0.08), 0 1px 5px rgba(166, 124, 82, 0.03) !important;
                            border-color: rgba(74, 103, 65, 0.2) !important;
                        }
                        .dark .fi-wi-stats-overview-stat-card, .dark .fi-card, .dark .fi-ta-ctn {
                            background-color: rgba(24, 24, 27, 0.85) !important;
                            border-color: rgba(255, 255, 255, 0.04) !important;
                        }
                        .dark .fi-wi-stats-overview-stat-card:hover, .dark .fi-card:hover {
                            border-color: rgba(74, 103, 65, 0.4) !important;
                        }
                        /* Custom scrollbar for organic feel */
                        ::-webkit-scrollbar {
                            width: 6px;
                            height: 6px;
                        }
                        ::-webkit-scrollbar-track {
                            background: transparent;
                        }
                        ::-webkit-scrollbar-thumb {
                            background: rgba(74, 103, 65, 0.2);
                            border-radius: 9999px;
                        }
                        ::-webkit-scrollbar-thumb:hover {
                            background: rgba(74, 103, 65, 0.4);
                            border-radius: 9999px;
                        }
                        /* Sleek badges */
                        .fi-badge {
                            border-radius: 9999px !important;
                            font-weight: 700 !important;
                            letter-spacing: 0.025em !important;
                        }
                    </style>
                ',
            )
            ->renderHook(
                'panels::user-menu.before',
                fn (): string => <<<'HTML'
                    <div class="flex items-center gap-4 mr-4 hidden md:flex">
                        <!-- AI Status -->
                        <div class="flex items-center gap-1.5 bg-emerald-500/10 dark:bg-emerald-500/5 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 px-2.5 py-1 rounded-full text-[10px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Mbek AI: Online
                        </div>
                        <!-- IoT Status -->
                        <div class="flex items-center gap-1.5 bg-sky-500/10 dark:bg-sky-500/5 text-sky-700 dark:text-sky-400 border border-sky-500/20 px-2.5 py-1 rounded-full text-[10px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                            IoT Broker: Active
                        </div>
                        <!-- Quick Link -->
                        <a href="/admin/documentations" class="flex items-center gap-1 text-[10px] uppercase tracking-wider font-extrabold text-gray-600 dark:text-gray-400 hover:text-[#4a6741] dark:hover:text-[#4a6741] transition-colors bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                            📚 Panduan
                        </a>
                    </div>
HTML
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => <<<'HTML'
                    <div class="fixed bottom-6 right-6 z-50">
                        <!-- Bubble Button -->
                        <button id="admin-chat-bubble-btn" class="w-14 h-14 bg-[#4a6741] text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-transform active:scale-95 border-2 border-white/20" style="box-shadow: 0 10px 25px rgba(74, 103, 65, 0.4);" aria-label="Tanya Asisten Qandang">
                            <svg id="admin-chat-bubble-icon" class="w-7 h-7 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <svg id="admin-chat-close-icon" class="w-7 h-7 hidden transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Chat Panel -->
                        <div id="admin-chat-panel" class="absolute bottom-18 right-0 w-[320px] sm:w-[360px] bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-3xl shadow-2xl overflow-hidden hidden transition-all duration-300 scale-95 opacity-0 origin-bottom-right">
                            <!-- Header -->
                            <div class="bg-[#4a6741] p-4 text-white flex items-center gap-3">
                                <div class="relative w-8 h-8 bg-white rounded-lg flex items-center justify-center p-1 shadow-md">
                                    <img src="/images/logo.webp" alt="Qandang Logo" class="w-full h-full object-contain">
                                    <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 bg-emerald-500 border-2 border-[#4a6741] rounded-full"></span>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-xs tracking-wide">Asisten Admin Qandang</h3>
                                    <p class="text-[9px] text-white/70 font-semibold uppercase tracking-wider">AI Copilot</p>
                                </div>
                            </div>

                            <!-- Tab Switcher -->
                            <div class="flex border-b border-gray-100 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-800/50 p-1">
                                <button id="admin-tab-ai-btn" class="flex-1 py-2 text-center text-xs font-bold transition-all duration-300 border-b-2 border-[#4a6741] text-[#4a6741]" onclick="switchAdminChatTab('ai')">
                                    🤖 Tanya AI
                                </button>
                                <button id="admin-tab-status-btn" class="flex-1 py-2 text-center text-xs font-bold transition-all duration-300 border-b-2 border-transparent text-gray-500 hover:text-[#4a6741]" onclick="switchAdminChatTab('status')">
                                    ⚙️ Status Server
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="p-4 flex flex-col justify-between min-h-[300px] max-h-[360px] text-gray-800 dark:text-gray-100">
                                <!-- Tab AI Panel -->
                                <div id="admin-panel-ai" class="flex flex-col flex-1">
                                    <!-- Messages -->
                                    <div id="admin-chat-messages" class="flex-1 overflow-y-auto max-h-[160px] space-y-3 pr-1 text-xs mb-3 scroll-smooth">
                                        <div class="flex gap-2">
                                            <div class="w-6 h-6 bg-[#4a6741]/10 rounded-lg flex items-center justify-center text-[10px] shrink-0 font-bold text-[#4a6741]">AI</div>
                                            <div class="bg-gray-100 dark:bg-zinc-800 p-3 rounded-2xl rounded-tl-none font-medium leading-relaxed max-w-[85%]">
                                                Halo Admin! Saya Copilot Qandang. Tanyakan instruksi manajemen kandang atau status integrasi di sini.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Suggested Prompts -->
                                    <div class="flex flex-wrap gap-1.5 mb-2.5">
                                        <button onclick="sendAdminPrompt('Bagaimana backup database?')" class="text-[9px] font-bold text-[#4a6741] bg-[#4a6741]/5 hover:bg-[#4a6741]/10 px-2 py-0.5 rounded-full transition-all">🗄️ Backup DB</button>
                                        <button onclick="sendAdminPrompt('Bagaimana melatih model AI?')" class="text-[9px] font-bold text-[#4a6741] bg-[#4a6741]/5 hover:bg-[#4a6741]/10 px-2 py-0.5 rounded-full transition-all">🧠 Latih Model AI</button>
                                        <button onclick="sendAdminPrompt('Konfigurasi Broker MQTT?')" class="text-[9px] font-bold text-[#4a6741] bg-[#4a6741]/5 hover:bg-[#4a6741]/10 px-2 py-0.5 rounded-full transition-all">📡 Broker MQTT</button>
                                    </div>

                                    <!-- Input -->
                                    <div class="flex gap-2 border-t border-gray-100 dark:border-zinc-800 pt-3">
                                        <input id="admin-chat-input" type="text" placeholder="Ketik perintah/pertanyaan..." class="flex-1 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-[#4a6741] transition-colors" onkeydown="handleAdminInputKey(event)">
                                        <button onclick="submitAdminQuery()" class="p-2 bg-[#4a6741] text-white rounded-xl hover:bg-[#3a5233] transition-colors shrink-0">
                                            <svg class="w-4 h-4 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tab Status Panel -->
                                <div id="admin-panel-status" class="hidden flex-col flex-1 gap-3 text-xs">
                                    <p class="font-bold text-gray-500 dark:text-gray-400 mb-1">Status Kesehatan Server:</p>
                                    <div class="space-y-2.5">
                                        <div class="flex justify-between items-center bg-gray-50 dark:bg-zinc-800 p-2 rounded-xl border border-gray-100 dark:border-zinc-700">
                                            <span class="font-medium text-gray-500">FastAPI AI Engine</span>
                                            <span class="text-emerald-600 font-bold flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Online (8500)
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center bg-gray-50 dark:bg-zinc-800 p-2 rounded-xl border border-gray-100 dark:border-zinc-700">
                                            <span class="font-medium text-gray-500">MQTT Broker (Mosquitto)</span>
                                            <span class="text-emerald-600 font-bold flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Connected (1883)
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center bg-gray-50 dark:bg-zinc-800 p-2 rounded-xl border border-gray-100 dark:border-zinc-700">
                                            <span class="font-medium text-gray-500">PostgreSQL Database</span>
                                            <span class="text-emerald-600 font-bold flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                Operational
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center bg-gray-50 dark:bg-zinc-800 p-2 rounded-xl border border-gray-100 dark:border-zinc-700">
                                            <span class="font-medium text-gray-500">PHP Queue Workers</span>
                                            <span class="text-amber-600 font-bold flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                Idle (0 Jobs)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function switchAdminChatTab(tab) {
                            const aiBtn = document.getElementById('admin-tab-ai-btn');
                            const statusBtn = document.getElementById('admin-tab-status-btn');
                            const aiPanel = document.getElementById('admin-panel-ai');
                            const statusPanel = document.getElementById('admin-panel-status');

                            if (tab === 'ai') {
                                aiBtn.className = 'flex-1 py-2 text-center text-xs font-bold transition-all duration-300 border-b-2 border-[#4a6741] text-[#4a6741]';
                                statusBtn.className = 'flex-1 py-2 text-center text-xs font-bold transition-all duration-300 border-b-2 border-transparent text-gray-500 hover:text-[#4a6741]';
                                aiPanel.classList.remove('hidden');
                                statusPanel.classList.add('hidden');
                            } else {
                                statusBtn.className = 'flex-1 py-2 text-center text-xs font-bold transition-all duration-300 border-b-2 border-[#4a6741] text-[#4a6741]';
                                aiBtn.className = 'flex-1 py-2 text-center text-xs font-bold transition-all duration-300 border-b-2 border-transparent text-gray-500 hover:text-[#4a6741]';
                                statusPanel.classList.remove('hidden');
                                aiPanel.classList.add('hidden');
                            }
                        }

                        function sendAdminPrompt(prompt) {
                            const input = document.getElementById('admin-chat-input');
                            if (input) {
                                input.value = prompt;
                                submitAdminQuery();
                            }
                        }

                        function handleAdminInputKey(e) {
                            if (e.key === 'Enter') {
                                submitAdminQuery();
                            }
                        }

                        const adminAiAnswers = {
                            "backup": "Jalankan perintah berikut di terminal server Anda untuk membackup database PostgreSQL: <br><code class='block bg-zinc-800 text-amber-400 p-2 rounded mt-1 font-mono text-[10px]'>pg_dump -U postgres qandang > backup.sql</code>",
                            "latih": "Latihan model AI dijalankan otomatis setiap hari Minggu jam 01:00 pagi menggunakan data histori berat di weight_logs. Anda bisa melatih manual lewat FastAPI: <code class='block bg-zinc-800 text-amber-400 p-2 rounded mt-1 font-mono text-[10px]'>POST /ai/train</code>",
                            "mqtt": "Broker MQTT berjalan di host local port 1883. Topik sensor utama: <code class='block bg-zinc-800 text-amber-400 p-2 rounded mt-1 font-mono text-[10px]'>qandang/barn/sensors</code>. Log ESP32 dipublikasikan di topik tersebut."
                        };

                        function submitAdminQuery() {
                            const input = document.getElementById('admin-chat-input');
                            const messages = document.getElementById('admin-chat-messages');
                            const query = input.value.trim();
                            if (!query) return;

                            input.value = '';

                            const userMsgHtml = `
                                <div class="flex gap-2 justify-end">
                                    <div class="bg-[#4a6741] text-white p-3 rounded-2xl rounded-tl-none font-medium leading-relaxed max-w-[85%]">
                                        ${query}
                                    </div>
                                </div>
                            `;
                            messages.insertAdjacentHTML('beforeend', userMsgHtml);
                            messages.scrollTop = messages.scrollHeight;

                            const typingId = 'admin-typing-' + Date.now();
                            const typingHtml = `
                                <div id="${typingId}" class="flex gap-2 animate-pulse">
                                    <div class="w-6 h-6 bg-[#4a6741]/10 rounded-lg flex items-center justify-center text-[10px] shrink-0 font-bold text-[#4a6741]">AI</div>
                                    <div class="bg-gray-100 dark:bg-zinc-800 p-3 rounded-2xl rounded-tl-none text-gray-500 font-bold">
                                        Mengetik...
                                    </div>
                                </div>
                            `;
                            messages.insertAdjacentHTML('beforeend', typingHtml);
                            messages.scrollTop = messages.scrollHeight;

                            let response = "Pertanyaan belum dikenali. Tanyakan tentang backup database, latihan model AI, atau MQTT broker untuk respon instan.";
                            const cleanQuery = query.toLowerCase();
                            for (const key in adminAiAnswers) {
                                if (cleanQuery.includes(key)) {
                                    response = adminAiAnswers[key];
                                    break;
                                }
                            }

                            setTimeout(() => {
                                const typingEl = document.getElementById(typingId);
                                if (typingEl) {
                                    typingEl.remove();
                                }
                                const aiMsgHtml = `
                                    <div class="flex gap-2 animate-fade-in">
                                        <div class="w-6 h-6 bg-[#4a6741]/10 rounded-lg flex items-center justify-center text-[10px] shrink-0 font-bold text-[#4a6741]">AI</div>
                                        <div class="bg-gray-100 dark:bg-zinc-800 p-3 rounded-2xl rounded-tl-none font-medium leading-relaxed max-w-[85%]">
                                            ${response}
                                        </div>
                                    </div>
                                `;
                                messages.insertAdjacentHTML('beforeend', aiMsgHtml);
                                messages.scrollTop = messages.scrollHeight;
                            }, 800);
                        }

                        document.addEventListener('DOMContentLoaded', () => {
                            const bubble = document.getElementById('admin-chat-bubble-btn');
                            const panel = document.getElementById('admin-chat-panel');
                            const bIcon = document.getElementById('admin-chat-bubble-icon');
                            const cIcon = document.getElementById('admin-chat-close-icon');

                            if (bubble && panel) {
                                bubble.addEventListener('click', () => {
                                    if (panel.classList.contains('hidden')) {
                                        panel.classList.remove('hidden');
                                        setTimeout(() => {
                                            panel.classList.remove('scale-95', 'opacity-0');
                                            panel.classList.add('scale-100', 'opacity-100');
                                        }, 50);
                                        bIcon.classList.add('hidden');
                                        cIcon.classList.remove('hidden');
                                    } else {
                                        panel.classList.remove('scale-100', 'opacity-100');
                                        panel.classList.add('scale-95', 'opacity-0');
                                        setTimeout(() => {
                                            panel.classList.add('hidden');
                                        }, 300);
                                        cIcon.classList.add('hidden');
                                        bIcon.classList.remove('hidden');
                                    }
                                });
                            }
                        });
                    </script>
HTML
            );;
    }
}
