<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row items-center gap-6 p-4">
            <!-- Cute SVG Animated Goat Mascot -->
            <div class="relative w-24 h-24 shrink-0 flex items-center justify-center bg-amber-100/50 dark:bg-amber-950/20 rounded-3xl border border-amber-200/40 dark:border-amber-900/20 shadow-inner overflow-hidden">
                <svg viewBox="0 0 100 100" class="w-20 h-20 goat-svg">
                    <!-- Ears -->
                    <path d="M 20 30 Q 5 25 15 45 Z" fill="#E6D3B9" stroke="#5C4D3C" stroke-width="2" class="ear-left" />
                    <path d="M 80 30 Q 95 25 85 45 Z" fill="#E6D3B9" stroke="#5C4D3C" stroke-width="2" class="ear-right" />
                    <path d="M 22 32 C 12 30 18 42 22 38" fill="#F4E8D6" />
                    <path d="M 78 32 C 88 30 82 42 78 38" fill="#F4E8D6" />
                    
                    <!-- Horns -->
                    <path d="M 35 25 C 32 10 20 8 20 8 C 20 8 30 12 38 22" fill="#B3A290" />
                    <path d="M 65 25 C 68 10 80 8 80 8 C 80 8 70 12 62 22" fill="#B3A290" />

                    <!-- Head/Face -->
                    <path d="M 25 35 Q 20 60 40 80 Q 50 85 60 80 Q 80 60 75 35 Z" fill="#F4E8D6" stroke="#5C4D3C" stroke-width="2.5" />
                    
                    <!-- Cute Hair tuft -->
                    <path d="M 40 25 Q 50 15 60 25 Q 50 20 40 25 Z" fill="#E6D3B9" stroke="#5C4D3C" stroke-width="1.5" />

                    <!-- Eyes -->
                    <circle cx="38" cy="48" r="4.5" fill="#2D241E" class="eye-left" />
                    <circle cx="62" cy="48" r="4.5" fill="#2D241E" class="eye-right" />
                    
                    <!-- Eye sparkles -->
                    <circle cx="39.5" cy="46.5" r="1.5" fill="#FFF" />
                    <circle cx="63.5" cy="46.5" r="1.5" fill="#FFF" />

                    <!-- Cheeks (Blush) -->
                    <ellipse cx="32" cy="56" rx="5" ry="3" fill="#FF8A8A" opacity="0.4" />
                    <ellipse cx="68" cy="56" rx="5" ry="3" fill="#FF8A8A" opacity="0.4" />

                    <!-- Snout & Nose -->
                    <ellipse cx="50" cy="65" rx="14" ry="10" fill="#E6D3B9" stroke="#5C4D3C" stroke-width="1.5" />
                    <path d="M 46 62 Q 50 67 54 62" fill="none" stroke="#5C4D3C" stroke-width="2.5" stroke-linecap="round" />
                    
                    <!-- Mouth (Smile) -->
                    <path d="M 44 70 Q 50 76 56 70" fill="none" stroke="#5C4D3C" stroke-width="2.5" stroke-linecap="round" class="mouth-smile" />

                    <!-- QR Tag Collar -->
                    <rect x="42" y="78" width="16" height="6" rx="2" fill="#4a6741" />
                    <rect x="47" y="84" width="6" height="6" rx="1" fill="#a67c52" class="qr-tag-anim" />
                </svg>
                
                <!-- Tiny floating grass animation -->
                <div class="absolute bottom-1 right-2 animate-bounce" style="animation-duration: 2s;">🌱</div>
            </div>
            
            <!-- Greeting & Stats Summary -->
            <div class="flex-1 space-y-2 text-center md:text-left">
                <h3 class="text-xl font-black text-[#2d241e] dark:text-white flex flex-col md:flex-row items-center gap-2">
                    <span>Selamat Datang di Qandang!</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-black bg-[#4a6741]/10 text-[#4a6741] dark:bg-[#4a6741]/20 dark:text-[#88b07d] rounded-full border border-[#4a6741]/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Sistem Online</span>
                    </span>
                </h3>
                <p class="text-sm font-semibold text-[#54483e] dark:text-[#a1a1aa] leading-relaxed max-w-2xl">
                    {{ $this->getGoatMessage() }}
                </p>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 pt-2">
                    <a href="{{ route('filament.admin.resources.goats.index') }}" class="text-xs font-extrabold text-[#a67c52] hover:text-[#4a6741] dark:text-[#d4a373] dark:hover:text-[#88b07d] flex items-center gap-1 transition-colors">
                        <span>Lihat Semua Kambing &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
        
        <style>
            /* Ear wiggle animation */
            .ear-left {
                animation: wiggle-ear-left 4s ease-in-out infinite;
                transform-origin: 22px 32px;
            }
            .ear-right {
                animation: wiggle-ear-right 4s ease-in-out infinite;
                transform-origin: 78px 32px;
            }
            @keyframes wiggle-ear-left {
                0%, 90%, 100% { transform: rotate(0deg); }
                95% { transform: rotate(-8deg); }
            }
            @keyframes wiggle-ear-right {
                0%, 90%, 100% { transform: rotate(0deg); }
                95% { transform: rotate(8deg); }
            }
            /* Eye blinking animation */
            .eye-left, .eye-right {
                animation: blink-eyes 5s infinite;
                transform-origin: center;
            }
            @keyframes blink-eyes {
                0%, 96%, 100% { transform: scaleY(1); }
                98% { transform: scaleY(0.1); }
            }
            /* QR Tag swinging */
            .qr-tag-anim {
                animation: swing-tag 3s ease-in-out infinite;
                transform-origin: 50% 80%;
            }
            @keyframes swing-tag {
                0%, 100% { transform: rotate(-5deg); }
                50% { transform: rotate(5deg); }
            }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
