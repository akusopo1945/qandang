<x-filament-panels::page>
    <div class="space-y-6" wire:init="loadAnalysis">
        @if(!$isLoaded)
            <!-- Enhanced Loading State -->
            <div class="flex flex-col items-center justify-center min-h-[400px] w-full" x-data="loadingState()">
                <div class="relative flex flex-col items-center p-8 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-primary-100 dark:border-gray-700 w-full max-w-lg">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 border-[10px] border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <x-heroicon-o-sparkles class="w-10 h-10 text-primary-600 animate-bounce" />
                        </div>
                    </div>
                    
                    <div class="text-center space-y-3 w-full">
                        <h2 class="text-xl font-black text-gray-900 dark:text-white" x-text="statusText">Qandang AI Sedang Berpikir...</h2>
                        <!-- Dynamic Progress Bar -->
                        <div class="mt-6 w-full h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-600 transition-all duration-300 ease-out" :style="'width: ' + progress + '%'"></div>
                        </div>
                        <p class="text-sm font-bold text-primary-600" x-text="progress + '%'"></p>
                    </div>
                </div>
            </div>

            <script>
                function loadingState() {
                    return {
                        progress: 0,
                        statusText: 'Menginisialisasi...',
                        init() {
                            let interval = setInterval(() => {
                                if (this.progress < 90) {
                                    this.progress += Math.random() * 5;
                                    if (this.progress > 30 && this.progress < 60) this.statusText = 'Menganalisis histori pertumbuhan...';
                                    if (this.progress >= 60) this.statusText = 'Menyusun laporan rekomendasi...';
                                } else {
                                    clearInterval(interval);
                                }
                            }, 300);
                        }
                    }
                }
            </script>
        @elseif(isset($analysisData['error']))
            <x-filament::section>
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-full mb-4">
                        <x-heroicon-o-exclamation-circle class="w-12 h-12 text-danger-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Analisis Terhenti</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-md mx-auto">{{ $analysisData['error'] }}</p>
                    <div class="mt-8 flex space-x-3">
                        <x-filament::button color="gray" icon="heroicon-o-arrow-left" tag="a" href="{{ GoatResource::getUrl('index') }}">
                            Kembali
                        </x-filament::button>
                        <x-filament::button icon="heroicon-o-arrow-path" wire:click="loadAnalysis">
                            Coba Lagi
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Forecast Card -->
                <x-filament::section icon="heroicon-m-presentation-chart-line" icon-color="primary">
                    <x-slot name="heading">Estimasi Pertumbuhan</x-slot>
                    <div class="py-4">
                        <div class="flex items-baseline space-x-2">
                            <span class="text-5xl font-black text-primary-600 tracking-tighter">{{ $analysisData['predicted_weight_next_month'] }}</span>
                            <span class="text-xl font-bold text-primary-400">kg</span>
                        </div>
                        <p class="text-sm font-medium text-gray-500 mt-1">Target berat badan bulan depan</p>
                        
                        <div class="mt-10 h-36 flex items-end space-x-6 px-4">
                             @php $max = max($analysisData['current_weight'], $analysisData['predicted_weight_next_month'], 1); @endphp
                             <div class="flex-1 flex flex-col items-center">
                                 <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t-xl" style="height: {{ ($analysisData['current_weight'] / $max) * 100 }}%"></div>
                                 <div class="text-[10px] font-bold text-gray-400 mt-3 uppercase tracking-wider">Sekarang</div>
                                 <div class="text-sm font-black text-gray-600 dark:text-gray-300">{{ $analysisData['current_weight'] }}kg</div>
                             </div>
                             <div class="flex-1 flex flex-col items-center">
                                 <div class="w-full bg-gradient-to-t from-primary-600 to-primary-400 rounded-t-xl shadow-lg shadow-primary-200 dark:shadow-none" style="height: {{ ($analysisData['predicted_weight_next_month'] / $max) * 100 }}%"></div>
                                 <div class="text-[10px] font-bold text-primary-500 mt-3 uppercase tracking-wider">Forecast</div>
                                 <div class="text-sm font-black text-primary-700 dark:text-primary-400">{{ $analysisData['predicted_weight_next_month'] }}kg</div>
                             </div>
                        </div>
                    </div>
                </x-filament::section>

                <!-- Health Score -->
                <x-filament::section icon="heroicon-m-shield-check" icon-color="warning">
                    <x-slot name="heading">Skor Kesehatan AI</x-slot>
                    <div class="flex flex-col items-center justify-center py-6">
                        <div class="relative flex items-center justify-center w-40 h-40">
                            <svg class="w-full h-full -rotate-90">
                                <circle class="text-gray-100 dark:text-gray-800" stroke-width="12" stroke="currentColor" fill="transparent" r="70" cx="80" cy="80" />
                                <circle class="text-warning-500" stroke-width="12" stroke-dasharray="439.8" stroke-dashoffset="{{ 439.8 * (1 - ($analysisData['confidence_score'])) }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="70" cx="80" cy="80" style="transition: stroke-dashoffset 1.5s ease-in-out;" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl font-black text-gray-900 dark:text-white">{{ ($analysisData['confidence_score'] * 100) }}</span>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Points</span>
                            </div>
                        </div>
                        <div class="mt-6 px-4 py-2 bg-warning-50 dark:bg-warning-900/20 rounded-full border border-warning-100 dark:border-warning-800">
                             <span class="text-sm font-bold text-warning-700 dark:text-warning-400">Analisis Keyakinan: Sangat Tinggi</span>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            <!-- Analysis Content -->
            <x-filament::section icon="heroicon-m-beaker" icon-color="success">
                <x-slot name="heading">Laporan Analisis Mendalam</x-slot>
                <div class="relative">
                    <div class="absolute -left-2 top-0 bottom-0 w-1 bg-primary-500 rounded-full opacity-50"></div>
                    <div class="pl-6 py-2 text-gray-700 dark:text-gray-300 leading-relaxed text-lg italic font-medium">
                        {!! nl2br(e($analysisData['analysis'])) !!}
                    </div>
                </div>
            </x-filament::section>
            
            <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
                <p class="text-xs text-gray-400 font-medium italic">* Analisis dihasilkan secara otomatis oleh Qandang AI Engine v2.5</p>
                <div class="flex space-x-3">
                    <x-filament::button color="gray" variant="outline" icon="heroicon-o-arrow-left" tag="a" href="{{ $this->getResource()::getUrl('index') }}">
                        Tutup Analisis
                    </x-filament::button>
                    <x-filament::button icon="heroicon-o-arrow-path" wire:click="loadAnalysis" color="warning">
                        Analisis Ulang
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
