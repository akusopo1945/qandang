@extends('layouts.public')

@section('title', 'Wishlist Saya - Qandang')
@section('meta_description', 'Simpan kambing yang Anda incar di wishlist Qandang Marketplace dan pantau ketersediaannya.')

@section('content')
<div class="container mx-auto px-4 max-w-6xl">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-[#2d241e] mb-2">Wishlist Saya</h1>
        <p class="text-[#6b5e51]">Simpan kambing impian Anda dan pantau ketersediaannya.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl mb-8">
            {{ session('success') }}
        </div>
    @endif

    @if($wishlistItems->isEmpty())
        <div class="bg-white p-12 rounded-[2.5rem] border border-[#a67c52]/10 text-center shadow-sm">
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-[#2d241e] mb-4">Wishlist Masih Kosong</h2>
            <p class="text-[#6b5e51] mb-8 text-lg">Belum ada kambing yang Anda simpan di wishlist.</p>
            <a href="{{ route('catalog') }}" class="inline-block bg-[#4a6741] text-white px-10 py-4 rounded-2xl font-bold hover:bg-[#3a5233] transition-colors shadow-lg shadow-[#4a6741]/20">
                Cari Kambing Sekarang
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($wishlistItems as $item)
                @php $goat = $item->goat; @endphp
                <div class="group bg-white rounded-[2.5rem] overflow-hidden border border-[#a67c52]/10 hover:border-[#4a6741] hover:shadow-2xl transition-all duration-500 relative">
                    <form action="{{ route('wishlist.remove', $goat->id) }}" method="POST" class="absolute top-4 right-4 z-10">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 bg-white/80 backdrop-blur rounded-full text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>

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
                        @if($goat->sale_status === 'sold')
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[2px]">
                                <span class="border-4 border-white text-white font-black text-2xl px-6 py-2 rotate-[-15deg] uppercase tracking-widest opacity-90">SOLD</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-xl text-[#2d241e] mb-1">{{ $goat->name }}</h3>
                            <p class="text-[#4a6741] font-extrabold text-lg">Rp {{ number_format($goat->price, 0, ',', '.') }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-center mb-6 mt-4">
                            <div class="bg-[#fdfaf5] p-2 rounded-lg">
                                <p class="text-[9px] text-[#6b5e51] uppercase font-bold">Bobot</p>
                                <p class="font-bold text-xs">{{ $goat->current_weight ?? '-' }} kg</p>
                            </div>
                            <div class="bg-[#fdfaf5] p-2 rounded-lg">
                                <p class="text-[9px] text-[#6b5e51] uppercase font-bold">Umur</p>
                                <p class="font-bold text-xs">{{ $goat->birth_date ? now()->diff($goat->birth_date)->y . ' Th' : '-' }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('catalog.show', $goat->qr_code) }}" class="flex-grow text-center border-2 border-[#4a6741] text-[#4a6741] py-3 rounded-xl font-bold hover:bg-[#4a6741] hover:text-white transition-all">
                                Detil
                            </a>
                            @if($goat->sale_status !== 'sold')
                                <form action="{{ route('cart.add') }}" method="POST" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="goat_id" value="{{ $goat->id }}">
                                    <button type="submit" class="p-3 bg-[#4a6741] text-white rounded-xl hover:bg-[#3a5233] transition-all" title="Tambah ke Keranjang">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
