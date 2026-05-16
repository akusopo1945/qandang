<x-filament-panels::page>
    <div class="space-y-6">
        @if(isset($analysisData['error']))
            <div class="p-4 text-red-700 bg-red-100 rounded-lg">
                Gagal memuat analisis: {{ $analysisData['error'] }}
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Forecast Card -->
                <x-filament::section>
                    <x-slot name="heading">Forecast Pertumbuhan</x-slot>
                    <div class="py-4">
                        <div class="text-3xl font-bold text-primary-600">
                            {{ $analysisData['predicted_weight_next_month'] }} kg
                        </div>
                        <p class="text-sm text-gray-500">Estimasi berat bulan depan</p>
                        
                        <div class="mt-6 h-32 flex items-end space-x-2">
                             @php $max = max($analysisData['current_weight'], $analysisData['predicted_weight_next_month']); @endphp
                             <div class="flex-1 flex flex-col items-center">
                                 <div class="w-full bg-gray-200 rounded-t" style="height: {{ ($analysisData['current_weight'] / $max) * 100 }}%"></div>
                                 <span class="text-[10px] mt-1">Sekarang</span>
                             </div>
                             <div class="flex-1 flex flex-col items-center">
                                 <div class="w-full bg-primary-500 rounded-t" style="height: {{ ($analysisData['predicted_weight_next_month'] / $max) * 100 }}%"></div>
                                 <span class="text-[10px] mt-1">Bulan Depan</span>
                             </div>
                        </div>
                    </div>
                </x-filament::section>

                <!-- Health Score -->
                <x-filament::section>
                    <x-slot name="heading">Kondisi Kesehatan</x-slot>
                    <div class="flex flex-col items-center justify-center py-4">
                        <div class="text-5xl font-black text-warning-500">
                            {{ ($analysisData['confidence_score'] * 100) }}%
                        </div>
                        <p class="mt-2 font-medium text-gray-600">Confidence Score</p>
                    </div>
                </x-filament::section>
            </div>

            <!-- Analysis Content -->
            <x-filament::section>
                <x-slot name="heading">Detail Analisis AI</x-slot>
                <div class="prose max-w-none dark:prose-invert">
                    {!! nl2br(e($analysisData['analysis'])) !!}
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
