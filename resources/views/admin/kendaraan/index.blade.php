@php
    $isAdmin = Auth::user()?->isAdmin() ?? false;
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.user';
@endphp

@extends($layout)

@section('title', 'Kendaraan Dinas')

@section('content')

<style>
    /* ===== HERO & BACKGROUND ===== */
    .gradient-bg {
        /* Warna gradasi persis seperti navbar: Hijau -> Teal -> Biru */
        background: linear-gradient(135deg, #22c55e 0%, #0d9488 50%, #2563eb 100%);
        position: relative;
        overflow: hidden;
    }
    
    .gradient-bg::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 60%);
        animation: rotate 20s linear infinite;
        pointer-events: none;
    }
    
    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* ===== ANIMASI MASUK (STAGGERED) ===== */
    @keyframes enterCard {
        from {
            opacity: 0;
            transform: translateY(40px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .animate-enter-card {
        opacity: 0;
        animation: enterCard 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    /* ===== ANIMASI KELUAR (HAPUS) ===== */
    @keyframes exitCard {
        to {
            opacity: 0;
            transform: scale(0.85) translateY(-10px);
            filter: blur(4px);
        }
    }

    .animate-exit-card {
        animation: exitCard 0.4s cubic-bezier(0.4, 0, 1, 1) forwards !important;
        pointer-events: none; /* Cegah klik ganda saat animasi keluar */
    }

    /* ===== GLASS CARD EFFECT ===== */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    }

    html.dark .glass-card {
        background: #0f172a !important;
        border-color: #1e293b !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7) !important;
    }
    
    /* ===== VEHICLE CARD ANIMATION ===== */
    .vehicle-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .vehicle-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.1), transparent);
        transition: left 0.5s;
        z-index: 10;
        pointer-events: none;
    }

    html.dark .vehicle-card::before {
        background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.05), transparent);
    }
    
    .vehicle-card:hover::before {
        left: 100%;
    }
    
    .vehicle-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(34, 197, 94, 0.15);
        border-color: #86efac !important;
    }

    html.dark .vehicle-card:hover {
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.85);
        border-color: #22c55e !important;
    }
    
    /* ===== SEARCH BOX ===== */
    .search-box {
        background: white;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
        border: 1px solid #e2e8f0;
    }

    html.dark .search-box {
        background: #0f172a !important;
        border-color: #1e293b !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5) !important;
    }
    
    .search-box:focus-within {
        box-shadow: 0 8px 30px rgba(34, 197, 94, 0.2);
        transform: translateY(-2px);
        border-color: #22c55e;
    }

    html.dark .search-box:focus-within {
        box-shadow: 0 8px 30px rgba(34, 197, 94, 0.15);
        border-color: #16a34a;
    }
    
    /* ===== STAT CARDS ===== */
    .stat-card {
        background: linear-gradient(135deg, #16a34a 0%, #0d9488 100%);       
        color: white;
        border-radius: 1.5rem;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s;
        border: 1px solid rgba(255,255,255,0.15);
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 50%);
    }
    
    /* ===== BUTTONS ===== */
    .btn-gradient {
        background: linear-gradient(135deg, #16a34a 0%, #2563eb 100%);
        border: 1px solid #15803d;
        transition: all 0.3s;
    }
    
    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(34, 197, 94, 0.3);
    }

    .btn-hero-add {
        background-color: #ffffff !important;
        background-image: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%) !important;
        color: #15803d !important;
        text-decoration: none !important;
        border: none !important;
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.2) !important;
        transition: all 0.2s ease;
    }
    
    .btn-hero-add:hover {
        background-image: linear-gradient(135deg, #ffffff 0%, #dcfce7 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.3) !important;
    }

    /* ===== LOADING SPINNER ===== */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>

<div class="min-h-screen pb-12 bg-slate-50 dark:bg-slate-900">
    <!-- Hero Section -->
    <div class="gradient-bg text-white py-12 mb-8 rounded-b-3xl shadow-xl">
        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-center md:text-left relative z-10">
                    <div class="flex items-center gap-3 justify-center md:justify-start mb-3">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-2xl shadow-inner border border-white/20">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white/80 tracking-wider uppercase">Monitoring Aset</p>
                            <h1 class="text-3xl md:text-4xl font-bold">Kendaraan Dinas</h1>
                        </div>
                    </div>
                    <p class="text-lg text-white/90 max-w-xl">Kelola dan pantau pajak kendaraan dengan mudah dan terintegrasi.</p>
                </div>
                
                @if($isAdmin)
                <a href="{{ route('admin.kendaraan.create') }}" class="btn-hero-add inline-flex items-center gap-2 px-8 py-4 rounded-full font-bold shadow-lg relative z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Kendaraan</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="relative z-10">
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-100 mb-1">Total Kendaraan</p>
                    <p class="text-3xl font-extrabold">{{ $kendaraan->count() }}</p>
                    <p class="text-xs text-blue-100 mt-1">Unit terdaftar</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                <div class="relative z-10">
                    <p class="text-xs font-bold uppercase tracking-widest opacity-80 mb-1">Pajak Mendesak</p>
                    <p class="text-3xl font-extrabold">
                        {{ $kendaraan->filter(fn($k) => ($k->pajakTahunanAktif?->sisa_hari ?? 999) <= 7 || ($k->pajakLimaTahunanAktif?->sisa_hari ?? 999) <= 7)->count() }}
                    </p>
                    <p class="text-xs opacity-70 mt-1">≤ 7 hari lagi</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="relative z-10">
                    <p class="text-xs font-bold uppercase tracking-widest opacity-80 mb-1">Status Aktif</p>
                    <p class="text-3xl font-extrabold">{{ $kendaraan->where('status', 'aktif')->count() }}</p>
                    <p class="text-xs opacity-70 mt-1">Siap digunakan</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);">
                <div class="relative z-10">
                    <p class="text-xs font-bold uppercase tracking-widest opacity-80 mb-1">Unit Kerja</p>
                    <p class="text-3xl font-extrabold">{{ $kendaraan->pluck('unit_kerja')->filter()->unique()->count() }}</p>
                    <p class="text-xs opacity-70 mt-1">Departemen berbeda</p>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="glass-card rounded-3xl p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-green-50 dark:bg-green-900/30 p-2 rounded-xl">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-base font-bold text-slate-800 dark:text-white">Keterangan Status Pajak</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="flex items-center gap-2 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/40 rounded-xl px-3 py-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600 dark:bg-red-500"></span>
                    <span class="text-xs font-semibold text-red-700 dark:text-red-300">≤ 7 hari</span>
                </div>
                <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/40 rounded-xl px-3 py-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-600 dark:bg-amber-500"></span>
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">8-30 hari</span>
                </div>
                <div class="flex items-center gap-2 bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/40 rounded-xl px-3 py-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 dark:bg-blue-500"></span>
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">31-60 hari</span>
                </div>
                <div class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/40 rounded-xl px-3 py-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 dark:bg-emerald-500"></span>
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Sudah Bayar</span>
                </div>
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-600 dark:bg-slate-400"></span>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">> 60 hari</span>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <form method="GET" class="glass-card rounded-3xl p-6 mb-8" onsubmit="showLoading(this)">
            <div class="grid md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="search-box flex items-center">
                        <svg class="w-5 h-5 text-slate-400 ml-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input name="search" value="{{ request('search') }}" placeholder="Cari plat nomor, pemakai..." class="w-full px-4 py-3 rounded-full border-0 focus:outline-none focus:ring-0 bg-transparent text-slate-900 dark:text-white placeholder-slate-400">
                    </div>
                </div>
                <input name="unit_kerja" value="{{ request('unit_kerja') }}" placeholder="Unit kerja" class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 focus:border-green-500 focus:ring-green-500 transition bg-white/50 dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 backdrop-blur-sm">
                <select name="status" class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 focus:border-green-500 focus:ring-green-500 transition bg-white/50 dark:bg-slate-800/80 text-slate-900 dark:text-white backdrop-blur-sm">
                    <option value="" class="dark:bg-slate-800">Semua status</option>
                    @foreach(['aktif','tidak dipakai','rusak'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status) class="dark:bg-slate-800">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 mt-4">
                <button type="submit" id="btn-filter" class="btn-gradient text-white px-6 py-3 rounded-xl font-bold flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all">
                    <svg id="icon-filter" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <svg id="spinner-filter" class="hidden w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="text-filter">Terapkan Filter</span>
                </button>
                <a href="{{ route('kendaraan.index') }}" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 px-6 py-3 rounded-xl font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </a>
            </div>
        </form>

        <!-- Vehicle Cards -->
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6" style="align-items: start;">
            @forelse($kendaraan as $item)
            <div class="vehicle-card glass-card rounded-3xl overflow-hidden flex flex-col h-full border border-slate-200 dark:border-slate-800 animate-enter-card" 
                 style="animation-delay: {{ $loop->index * 100 }}ms;" 
                 id="vehicle-card-{{ $item->id }}">
                
                <!-- Image Header -->
                <div class="relative h-52 bg-slate-800 dark:bg-black overflow-hidden flex-shrink-0 group/img-container">
                    @if($item->foto_url)
                        <img src="{{ $item->foto_url }}"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover/img-container:scale-110"
                             alt="{{ $item->plat_nomor }}">
                        
                        <!-- Overlay & Zoom Button -->
                        <div class="absolute inset-0 bg-black/0 hover:bg-black/30 transition-all flex items-end justify-end p-4">
                            <button type="button"
                                    onclick="openPreview('{{ $item->foto_url }}', '{{ $item->plat_nomor }}')"
                                    class="opacity-0 hover:opacity-100 bg-white/90 text-slate-800 text-xs font-bold px-4 py-2 rounded-full flex items-center gap-2 transition-all transform translate-y-4 hover:translate-y-0 shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                                Preview Foto
                            </button>
                        </div>
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900 dark:from-slate-900 dark:to-black">
                            <svg class="w-16 h-16 text-green-500/50 dark:text-green-900/50 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                            </svg>
                            <span class="text-slate-400 dark:text-slate-500 text-sm font-medium">Tidak ada foto</span>
                        </div>
                    @endif

                    <!-- Status badge -->
                    <div class="absolute top-4 right-4 pointer-events-none">
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold shadow-lg backdrop-blur-md
                            {{ $item->status === 'aktif' ? 'bg-green-500/90 text-white' : 'bg-slate-600/90 text-white' }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-5 flex flex-col flex-1">
                    <!-- Label + Judul -->
                    <a href="{{ route($isAdmin ? 'admin.kendaraan.show' : 'kendaraan.show', $item) }}" class="block mb-4 group/link">
                        <span class="inline-block px-3 py-1 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg text-[10px] font-bold tracking-wider uppercase mb-2 border border-green-100 dark:border-green-800">
                            Kendaraan Dinas
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white leading-tight group-hover/link:text-green-600 dark:group-hover/link:text-green-400 transition-colors">{{ $item->plat_nomor }}</h2>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ $item->merk ?: '—' }}</p>
                    </a>

                    <!-- Unit Kerja -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl px-4 py-3 mb-4 border border-slate-100 dark:border-slate-700/50">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider mb-0.5">Unit Kerja</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200 text-sm">{{ $item->unit_kerja ?: 'Belum ditentukan' }}</p>
                    </div>

                    <!-- Pajak badges -->
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        @foreach([['Pajak Tahunan', $item->pajakTahunanAktif], ['Pajak 5 Tahunan', $item->pajakLimaTahunanAktif]] as [$label, $pajak])
                        <div class="rounded-xl p-3 border border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-800/30">
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mb-1.5 font-semibold uppercase">{{ $label }}</p>
                            @if($pajak)
                                @include('kendaraan.partials.badge-pajak', ['pajak' => $pajak])
                            @else
                                <span class="text-xs text-slate-400 dark:text-slate-500 italic">Belum ada data</span>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <!-- Action buttons — selalu di bawah -->
                    <div class="mt-auto flex gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route($isAdmin ? 'admin.kendaraan.show' : 'kendaraan.show', $item) }}"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 active:scale-95 shadow-md shadow-green-200/50 dark:shadow-none"
                           style="background: linear-gradient(135deg,#22c55e,#16a34a);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Detail
                        </a>
                        @if($isAdmin)
                        <a href="{{ route('admin.kendaraan.edit', $item) }}"
                           class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 active:scale-95 shadow-md shadow-blue-200/50 dark:shadow-none"
                           style="background: linear-gradient(135deg,#3b82f6,#2563eb);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <button
                            type="button"
                            onclick="confirmDelete({{ $item->id }}, '{{ $item->plat_nomor }}')"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 active:scale-95 shadow-md shadow-red-200/50 dark:shadow-none"
                            style="background: linear-gradient(135deg,#ef4444,#dc2626);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        <form id="delete-form-{{ $item->id }}" action="{{ route('admin.kendaraan.destroy', $item) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="md:col-span-2 xl:col-span-3 glass-card rounded-3xl p-16 text-center animate-enter-card">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-50 dark:bg-slate-800 mb-6">
                    <svg class="w-10 h-10 text-green-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-xl font-bold text-slate-800 dark:text-white mb-2">Belum ada data kendaraan</p>
                <p class="text-slate-500 dark:text-slate-400 mb-6">Silakan tambahkan kendaraan baru untuk memulai pendataan.</p>
                @if($isAdmin)
                <a href="{{ route('admin.kendaraan.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Sekarang
                </a>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="image-preview-modal" class="hidden fixed inset-0 z-[60] items-center justify-center bg-black/90 backdrop-blur-md p-4 transition-opacity duration-300">
        <div class="relative w-full max-w-5xl mx-auto">
            <!-- Tombol X -->
            <button onclick="closePreview()"
                    class="absolute -top-12 right-0 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-red-500 hover:text-white transition-all border border-white/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="bg-black rounded-2xl overflow-hidden shadow-2xl border border-white/10">
                <img id="preview-img" src="" alt="" class="w-full max-h-[80vh] object-contain">
            </div>
            <p id="preview-caption" class="mt-4 text-center text-white font-bold text-lg drop-shadow-md"></p>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ── Image Preview ────────────────────────────────────
    function openPreview(url, caption) {
        document.getElementById('preview-img').src = url;
        document.getElementById('preview-caption').textContent = caption;
        const modal = document.getElementById('image-preview-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closePreview() {
        const modal = document.getElementById('image-preview-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        setTimeout(() => {
             document.getElementById('preview-img').src = '';
        }, 300);
    }

    // Tutup modal kalau klik backdrop
    document.getElementById('image-preview-modal').addEventListener('click', function(e) {
        if (e.target === this) closePreview();
    });

    // Tutup modal pakai Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePreview();
    });

    // ── Delete Confirm ───────────────────────────────────
    function confirmDelete(id, platNomor) {
        Swal.fire({
            title: 'Hapus Kendaraan?',
            html: `Yakin ingin menghapus kendaraan <strong>${platNomor}</strong>?<br><small class="text-gray-500 mt-2 block">Semua data pajak terkait juga akan terhapus permanen.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading state di SweetAlert
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Tambahkan animasi keluar pada card
                const card = document.getElementById('vehicle-card-' + id);
                if (card) {
                    card.classList.add('animate-exit-card');
                }

                // Tunggu animasi selesai (400ms), baru submit form
                setTimeout(() => {
                    document.getElementById('delete-form-' + id).submit();
                }, 450);
            }
        });
    }

    // ── Loading State saat Filter ────────────────────────
    function showLoading(form) {
        const btn = document.getElementById('btn-filter');
        const spinner = document.getElementById('spinner-filter');
        const icon = document.getElementById('icon-filter');
        const text = document.getElementById('text-filter');

        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        }
        if (spinner) spinner.classList.remove('hidden');
        if (icon) icon.classList.add('hidden');
        if (text) text.textContent = 'Memuat...';
    }
</script>
@endpush
@endsection