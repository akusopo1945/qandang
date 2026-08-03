@extends('layouts.public')

@section('title', $goat->name . ' - Qandang Marketplace')
@section('meta_description', 'Lihat detail kambing ' . $goat->name . ' di Qandang Marketplace, termasuk harga, bobot, tinggi, dan status jual.')

@section('extra_meta')
    <!-- Deep Linking for Mobile App -->
    <meta property="al:android:url" content="qandang://goat/{{ $goat->qr_code }}">
    <meta property="al:android:package" content="com.qandang.app">
    <meta property="al:android:app_name" content="Qandang">
    <meta property="al:ios:url" content="qandang://goat/{{ $goat->qr_code }}">
    <meta property="al:ios:app_store_id" content="123456789">
@endsection

@section('content')
<div class="container mx-auto px-4">
    <!-- App Prompt Banner (Optional/Hidden by default) -->
    <div data-dismissible-banner class="bg-[#4a6741] text-white p-4 rounded-2xl mb-8 flex justify-between items-center shadow-lg">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo.webp') }}" alt="Logo" width="450" height="450" class="h-10 bg-white rounded-lg p-1" loading="eager" decoding="async">
            <div>
                <p class="font-bold text-sm">Lihat harian di aplikasi?</p>
                <p class="text-[10px] opacity-80">Pantau grafik pertumbuhan lebih detail</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="#" class="bg-white text-[#4a6741] px-4 py-2 rounded-xl text-xs font-bold">Buka App</a>
            <button type="button" data-dismissible-banner-close class="p-1 opacity-50 hover:opacity-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
    <nav class="flex mb-8 text-sm font-medium text-[#6b5e51]" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li><a href="/" class="hover:text-[#4a6741]">Home</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('catalog') }}" class="hover:text-[#4a6741]">Katalog</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-[#2d241e]">{{ $goat->name }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Image Gallery -->
        <div class="space-y-4">
            <div class="bg-white rounded-[3rem] overflow-hidden border border-[#a67c52]/10 shadow-xl aspect-square">
                @if($goat->image)
                    <img src="{{ Storage::url($goat->image) }}" alt="{{ $goat->name }}" width="1200" height="1200" loading="eager" decoding="async" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-[#4a6741]/5 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-[#4a6741]/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        <!-- Details -->
        <div class="flex flex-col">
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="bg-[#4a6741]/10 text-[#4a6741] px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest border border-[#4a6741]/20">
                        {{ $goat->breed ?? 'Ras Lokal' }}
                    </span>
                    @if($goat->sale_status === 'auction')
                        <span class="bg-orange-500 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest shadow-lg">
                            Dilelang
                        </span>
                    @endif
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-[#2d241e] mb-2">{{ $goat->name }}</h1>
                <p class="text-lg text-[#6b5e51] font-mono mb-6">ID: {{ $goat->qr_code }}</p>
                
                <div class="bg-white p-6 rounded-3xl border border-[#a67c52]/10 shadow-sm inline-block">
                    <p class="text-sm text-[#6b5e51] uppercase font-bold tracking-widest mb-1">Harga</p>
                    <p class="text-4xl font-extrabold text-[#4a6741]">Rp {{ number_format($goat->price, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-4 rounded-2xl border border-[#a67c52]/10 text-center">
                    <p class="text-[10px] text-[#6b5e51] uppercase font-bold tracking-tighter">Bobot</p>
                    <p class="font-extrabold text-lg">{{ $goat->current_weight ?? $goat->initial_weight ?? '-' }} kg</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-[#a67c52]/10 text-center">
                    <p class="text-[10px] text-[#6b5e51] uppercase font-bold tracking-tighter">Tinggi</p>
                    <p class="font-extrabold text-lg">{{ $goat->height ?? '-' }} cm</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-[#a67c52]/10 text-center">
                    <p class="text-[10px] text-[#6b5e51] uppercase font-bold tracking-tighter">Kelamin</p>
                    <p class="font-extrabold text-lg">{{ $goat->gender === 'male' ? 'Jantan' : 'Betina' }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-[#a67c52]/10 text-center">
                    <p class="text-[10px] text-[#6b5e51] uppercase font-bold tracking-tighter">Umur</p>
                    <p class="font-extrabold text-lg">{{ $goat->birth_date ? now()->diff($goat->birth_date)->y . ' Th' : '-' }}</p>
                </div>
            </div>

            <div class="prose prose-stone mb-8">
                <h3 class="text-xl font-bold text-[#2d241e]">Deskripsi</h3>
                <p class="text-[#6b5e51]">{{ $goat->description ?? 'Tidak ada deskripsi tambahan untuk kambing ini.' }}</p>
            </div>

            <div class="mt-auto flex flex-col sm:flex-row gap-4">
                <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="goat_id" value="{{ $goat->id }}">
                    <button type="submit" class="w-full bg-[#4a6741] text-white py-4 rounded-2xl font-bold text-lg hover:bg-[#3a5233] transition-all transform hover:-translate-y-1 shadow-xl shadow-[#4a6741]/20">
                        Tambah ke Keranjang
                    </button>
                </form>
                <form action="{{ route('wishlist.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="goat_id" value="{{ $goat->id }}">
                    <button type="submit" class="flex items-center justify-center gap-2 px-8 py-4 bg-white border border-[#a67c52]/20 rounded-2xl font-bold text-[#2d241e] hover:bg-[#fdfaf5] transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        Wishlist
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
