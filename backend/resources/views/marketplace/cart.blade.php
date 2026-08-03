@extends('layouts.public')

@section('title', 'Keranjang Belanja - Qandang')
@section('meta_description', 'Tinjau kambing yang sudah Anda masukkan ke keranjang belanja Qandang Marketplace sebelum checkout.')

@section('content')
<div class="container mx-auto px-4 max-w-4xl">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-[#2d241e] mb-2">Keranjang Belanja</h1>
        <p class="text-[#6b5e51]">Selesaikan pesanan bibit kambing unggulan Anda.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl mb-8">
            {{ session('success') }}
        </div>
    @endif

    @if($cartItems->isEmpty())
        <div class="bg-white p-12 rounded-[2.5rem] border border-[#a67c52]/10 text-center shadow-sm">
            <div class="w-20 h-20 bg-[#4a6741]/5 text-[#4a6741] rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-[#2d241e] mb-4">Keranjang Kosong</h2>
            <p class="text-[#6b5e51] mb-8 text-lg">Anda belum menambahkan kambing ke keranjang belanja.</p>
            <a href="{{ route('catalog') }}" class="inline-block bg-[#4a6741] text-white px-10 py-4 rounded-2xl font-bold hover:bg-[#3a5233] transition-colors shadow-lg shadow-[#4a6741]/20">
                Lihat Katalog
            </a>
        </div>
    @else
        <div class="bg-white rounded-[2.5rem] border border-[#a67c52]/10 shadow-sm overflow-hidden mb-8">
            <div class="p-8 md:p-12 space-y-8">
                @foreach($cartItems as $item)
                    <div class="flex flex-col md:flex-row items-center gap-6 pb-8 border-b border-[#a67c52]/10 last:border-0 last:pb-0">
                        <div class="w-full md:w-32 aspect-square rounded-2xl overflow-hidden shrink-0 border border-[#a67c52]/10">
                            @if($item->goat->image)
                                <img src="{{ Storage::url($item->goat->image) }}" alt="{{ $item->goat->name }}" width="800" height="800" loading="lazy" decoding="async" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-[#4a6741]/5 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#4a6741]/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow text-center md:text-left">
                            <h3 class="font-extrabold text-xl text-[#2d241e] mb-1">{{ $item->goat->name }}</h3>
                            <p class="text-sm text-[#6b5e51] font-mono mb-2">{{ $item->goat->qr_code }}</p>
                            <div class="flex flex-wrap justify-center md:justify-start gap-4 text-xs font-bold text-[#6b5e51] uppercase tracking-wider">
                                <span>Bobot: {{ $item->goat->current_weight }} kg</span>
                                <span>Umur: {{ $item->goat->birth_date ? now()->diff($item->goat->birth_date)->y . ' Th' : '-' }}</span>
                            </div>
                        </div>
                        <div class="text-center md:text-right shrink-0">
                            <p class="text-xs text-[#6b5e51] font-bold uppercase tracking-wider mb-1">Harga Satuan</p>
                            <p class="text-[#4a6741] font-black text-xl mb-4">Rp {{ number_format($item->goat->price, 0, ',', '.') }}</p>
                            
                            <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm flex items-center gap-1 mx-auto md:ml-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-[#fdfaf5] p-8 md:p-12 border-t border-[#a67c52]/10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <p class="text-[#6b5e51] font-bold uppercase tracking-[0.2em] text-sm mb-1 text-center md:text-left">Total Pembayaran</p>
                    <p class="text-[#2d241e] font-black text-4xl">Rp {{ number_format($total, 0, ',', '.') }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                    <a href="{{ route('catalog') }}" class="px-8 py-4 text-center font-bold text-[#6b5e51] hover:text-[#4a6741] transition-colors">
                        Tambah Lagi
                    </a>
                    <form action="{{ route('checkout') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full bg-[#4a6741] text-white px-12 py-4 rounded-2xl font-black text-lg shadow-xl shadow-[#4a6741]/20 hover:bg-[#3a5233] transition-all transform hover:-translate-y-1">
                            Checkout Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="bg-orange-50 p-6 rounded-3xl border border-orange-200 flex items-start gap-4">
            <div class="shrink-0 w-10 h-10 bg-orange-500 text-white rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-orange-900 mb-1">Informasi Pemesanan</h4>
                <p class="text-orange-800 text-sm leading-relaxed">Setelah melakukan checkout, tim admin Qandang akan segera menghubungi Anda melalui WhatsApp untuk koordinasi pengiriman dan instruksi pembayaran resmi.</p>
            </div>
        </div>
    @endif
</div>
@endsection
