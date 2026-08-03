@extends('layouts.public')

@section('title', 'Katalog Kambing - Qandang Marketplace')
@section('meta_description', 'Jelajahi katalog kambing Qandang Marketplace untuk melihat bibit unggul, harga, bobot, dan detail ternak yang tersedia.')

@section('content')
<div class="container mx-auto px-4">
    <div class="text-center mb-16">
        <h1 class="text-4xl md:text-6xl font-extrabold text-[#2d241e] mb-4">Katalog Ternak</h1>
        <p class="text-[#6b5e51] text-lg max-w-2xl mx-auto">Temukan bibit unggul dan kambing kualitas terbaik langsung dari peternakan kami.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Filters (Sidebar) -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-8 rounded-[2rem] border border-[#a67c52]/10 shadow-sm">
                <h3 class="font-bold text-xl mb-6">Filter</h3>
                
                <form action="{{ route('catalog') }}" method="GET" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-[#6b5e51] uppercase tracking-widest mb-2">Jenis</label>
                        <select name="type" class="w-full bg-[#fdfaf5] border-[#a67c52]/20 rounded-xl focus:ring-[#4a6741] focus:border-[#4a6741]">
                            <option value="">Semua</option>
                            <option value="for_sale" {{ request('type') == 'for_sale' ? 'selected' : '' }}>Dijual</option>
                            <option value="auction" {{ request('type') == 'auction' ? 'selected' : '' }}>Lelang</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-[#4a6741] text-white py-3 rounded-xl font-bold hover:bg-[#3a5233] transition-colors">
                        Terapkan Filter
                    </button>
                </form>
            </div>
        </div>

        <!-- Catalog Grid -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($goats as $goat)
                    <div class="group bg-white rounded-[2.5rem] overflow-hidden border border-[#a67c52]/10 hover:border-[#4a6741] hover:shadow-2xl transition-all duration-500">
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
                        <div class="p-6 {{ $goat->sale_status === 'sold' ? 'opacity-75' : '' }}">
                            <h3 class="font-bold text-xl text-[#2d241e] mb-1">{{ $goat->name }}</h3>
                            <p class="text-[#4a6741] font-extrabold text-lg mb-4">Rp {{ number_format($goat->price, 0, ',', '.') }}</p>
                            
                            <div class="grid grid-cols-2 gap-2 text-center mb-6">
                                <div class="bg-[#fdfaf5] p-2 rounded-lg">
                                    <p class="text-[9px] text-[#6b5e51] uppercase font-bold">Bobot</p>
                                    <p class="font-bold text-xs">{{ $goat->current_weight ?? '-' }} kg</p>
                                </div>
                                <div class="bg-[#fdfaf5] p-2 rounded-lg">
                                    <p class="text-[9px] text-[#6b5e51] uppercase font-bold">Umur</p>
                                    <p class="font-bold text-xs">{{ $goat->birth_date ? now()->diff($goat->birth_date)->y . ' Th' : '-' }}</p>
                                </div>
                            </div>

                            @if($goat->sale_status === 'sold')
                                <button disabled class="w-full text-center border-2 border-gray-400 text-gray-400 py-3 rounded-xl font-bold cursor-not-allowed">
                                    Sudah Terjual
                                </button>
                            @else
                                <div class="flex gap-2">
                                    <a href="{{ route('catalog.show', $goat->qr_code) }}" class="flex-grow text-center border-2 border-[#4a6741] text-[#4a6741] py-3 rounded-xl font-bold hover:bg-[#4a6741] hover:text-white transition-all">
                                        Lihat Detil
                                    </a>
                                    <form action="{{ route('cart.add') }}" method="POST" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="goat_id" value="{{ $goat->id }}">
                                        <button type="submit" class="p-3 border-2 border-[#4a6741] text-[#4a6741] rounded-xl hover:bg-[#4a6741] hover:text-white transition-all" title="Tambah ke Keranjang">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-24 text-center bg-white rounded-[3rem] border border-dashed border-[#a67c52]/20">
                        <p class="text-[#6b5e51] font-medium text-lg">Tidak ada kambing yang sesuai dengan kriteria filter.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-12">
                {{ $goats->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
