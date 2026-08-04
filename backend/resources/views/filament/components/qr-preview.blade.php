<div class="flex flex-col justify-center items-center p-6 text-center">
    <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100 mb-4 inline-block">
        {!! $qrCode !!}
    </div>
    <div class="text-sm text-gray-500 font-mono select-all bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 mb-2">
        {{ $qrCodeText }}
    </div>
    <p class="text-xs text-gray-400">Arahkan kamera HP ke QR Code di atas untuk memindai.</p>
    <p class="text-[10px] text-gray-300 mt-1">URL: <a href="{{ $qrCodeUrl }}" target="_blank" class="text-primary-600 underline">{{ $qrCodeUrl }}</a></p>
</div>
