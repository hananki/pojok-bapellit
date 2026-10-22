@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $isAdmin = $user?->isAdmin() ?? false;
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.user';
@endphp

@extends($layout)
@section('title', 'Presensi Saya')
@section('content')

<style>
    /* ===== HERO ANIMASI & GRADASI ===== */
    .bapelit-hero {
        background: linear-gradient(135deg, #22c55e 0%, #0d9488 50%, #2563eb 100%);
        position: relative;
        overflow: hidden;
    }

    /* Animasi Lingkaran Berputar */
    .bapelit-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 60%);
        animation: rotate 20s linear infinite;
        pointer-events: none;
        z-index: 0;
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .bapelit-hero > * {
        position: relative;
        z-index: 1;
    }

    /* ===== ANIMASI MASUK (FADE UP) ===== */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up {
        animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
    .delay-100 { animation-delay: 100ms; }
    .delay-200 { animation-delay: 200ms; }
    .delay-300 { animation-delay: 300ms; }
    .delay-400 { animation-delay: 400ms; }

    /* ===== STAT CARDS ===== */
    .stat-card {
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-5px) scale(1.02);
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%; right: -50%;
        width: 100%; height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 60%);
        opacity: 0;
        transition: opacity 0.3s;
    }
    .stat-card:hover::after {
        opacity: 1;
    }
    
    .stat-card-hadir   { background: linear-gradient(135deg,#10b981,#059669); box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4); }
    .stat-card-tidak   { background: linear-gradient(135deg,#ef4444,#b91c1c); box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4); }
    .stat-card-persen  { background: linear-gradient(135deg,#3b82f6,#2563eb); box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4); }
    .stat-card-bulan   { background: linear-gradient(135deg,#14b8a6,#0d9488); box-shadow: 0 10px 25px -5px rgba(20, 184, 166, 0.4); }

    /* ===== TOMBOL SCAN (PULSE EFFECT) ===== */
    @keyframes pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(250, 204, 21, 0); }
        100% { box-shadow: 0 0 0 0 rgba(250, 204, 21, 0); }
    }
    .bapelit-hero a.btn-scan-absensi {
        background-image: linear-gradient(135deg, #facc15 0%, #eab308 100%);
        color: #1e3a8a;
        text-decoration: none;
        border: none;
        animation: pulse-ring 2s infinite;
        transition: all 0.3s ease;
    }
    .bapelit-hero a.btn-scan-absensi:hover {
        background-image: linear-gradient(135deg, #fde047 0%, #facc15 100%);
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 15px 30px -5px rgba(250, 204, 21, 0.5);
        animation: none;
    }
    .bapelit-hero a.btn-scan-absensi svg { stroke: #1e3a8a; }

    /* ===== KALENDER (WARNA ASLI) ===== */
    .kalender-cell {
        min-height: 120px;
    }
    .kalender-header-hari {
        background-color: #1e3a8a !important;
        color: #ffffff !important;
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 12px 4px !important;
        letter-spacing: 0.1em !important;
        text-transform: uppercase !important;
        border-right: 1px solid #e5e7eb !important;
        border-bottom: 1px solid #e5e7eb !important;
    }
    .kalender-header-weekend {
        background-color: #fef2f2 !important;
        color: #ef4444 !important;
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 12px 4px !important;
        letter-spacing: 0.1em !important;
        text-transform: uppercase !important;
        border-right: 1px solid #e5e7eb !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    /* ===== ANIMASI LIST (STAGGERED) ===== */
    @keyframes listIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-list-in {
        animation: listIn 0.4s ease-out forwards;
        opacity: 0;
    }

    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>

<div x-data="presensiApp()" class="pb-10 bg-slate-50 min-h-screen">

    {{-- ===== HERO ===== --}}
    <div class="bapelit-hero text-white px-4 sm:px-8 pt-8 pb-8 rounded-b-3xl shadow-xl">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4 animate-fade-up">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0 ring-1 ring-white/30 shadow-inner">
                    <svg class="w-8 h-8 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                 M9 5a2 2 0 002 2h2a2 2 0 002-2
                                 M9 5a2 2 0 012-2h2a2 2 0 012 2
                                 m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold tracking-widest text-white/80 uppercase mb-0.5">POJOK BAPELIT</p>
                    <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight drop-shadow-sm">Presensi Saya</h1>
                    <p class="text-sm text-white/90 mt-0.5">Riwayat dan statistik kehadiran Anda</p>
                </div>
            </div>
            <a href="{{ route('absensi.scan-page') }}"
               class="btn-scan-absensi inline-flex items-center gap-2.5 px-6 py-3 font-bold rounded-2xl self-start sm:self-auto animate-fade-up delay-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4
                             m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z
                             m12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z
                             M5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Scan Absensi
            </a>
        </div>
    </div>

    {{-- ===== STATS ===== --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="stat-card stat-card-hadir rounded-2xl p-5 text-white animate-fade-up delay-100">
                <div class="relative z-10">
                    <p class="text-xs font-bold tracking-widest opacity-80 uppercase mb-2">Hadir</p>
                    <p class="text-4xl font-extrabold">{{ $totalHadir }}</p>
                    <p class="text-xs opacity-70 mt-1">dari {{ $totalHadir + $totalTidak }} diabsen</p>
                </div>
            </div>
            <div class="stat-card stat-card-tidak rounded-2xl p-5 text-white animate-fade-up delay-200">
                <div class="relative z-10">
                    <p class="text-xs font-bold tracking-widest opacity-80 uppercase mb-2">Tidak Hadir</p>
                    <p class="text-4xl font-extrabold">{{ $totalTidak }}</p>
                    <p class="text-xs opacity-70 mt-1">dari {{ $totalHadir + $totalTidak }} diabsen</p>
                </div>
            </div>
            <div class="stat-card stat-card-persen rounded-2xl p-5 text-white animate-fade-up delay-300">
                <div class="relative z-10">
                    <p class="text-xs font-bold tracking-widest opacity-80 uppercase mb-2">Kehadiran</p>
                    <p class="text-4xl font-extrabold">{{ $persentaseHadir }}<span class="text-2xl">%</span></p>
                    <p class="text-xs opacity-70 mt-1">tingkat kehadiran</p>
                </div>
            </div>
            <div class="stat-card stat-card-bulan rounded-2xl p-5 text-white animate-fade-up delay-400">
                <div class="relative z-10">
                    <p class="text-xs font-bold tracking-widest opacity-80 uppercase mb-2">Bulan Ini</p>
                    <p class="text-4xl font-extrabold">{{ $hadirBulanIni }}</p>
                    <p class="text-xs opacity-70 mt-1">sesi hadir bulan ini</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MAIN CARD ===== --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-8 animate-fade-up delay-200">

        @if($riwayat->total() == 0)
        <div class="mb-6 p-5 bg-blue-50 border border-blue-200 rounded-2xl flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-blue-900 mb-1">Cara Absen</p>
                <ol class="text-sm text-blue-800 space-y-0.5 list-decimal list-inside">
                    <li>Minta admin menampilkan <strong>QR Code</strong> sesi</li>
                    <li>Klik tombol <strong>Scan Absensi</strong> di atas</li>
                    <li>Arahkan kamera ke QR Code atau masukkan token</li>
                    <li>Kehadiran otomatis tercatat ✅</li>
                </ol>
            </div>
        </div>
        @endif

        {{-- ===== TOOLBAR ===== --}}
        <div class="bg-white rounded-t-2xl border border-gray-200 shadow-sm px-4 py-3">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-model="search" type="text" placeholder="Cari nama sesi..."
                           class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm
                                  focus:bg-white focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 focus:outline-none transition">
                </div>

                <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                    <button @click="filterStatus = ''"
                            :class="filterStatus === '' ? 'bg-white text-blue-900 shadow font-bold' : 'text-gray-500 hover:text-gray-800'"
                            class="px-4 py-2 rounded-lg text-sm transition">Semua</button>
                    <button @click="filterStatus = 'hadir'"
                            :class="filterStatus === 'hadir' ? 'bg-white text-green-700 shadow font-bold' : 'text-gray-500 hover:text-gray-800'"
                            class="px-4 py-2 rounded-lg text-sm transition">Hadir</button>
                    <button @click="filterStatus = 'tidak'"
                            :class="filterStatus === 'tidak' ? 'bg-white text-red-600 shadow font-bold' : 'text-gray-500 hover:text-gray-800'"
                            class="px-4 py-2 rounded-lg text-sm transition">Tidak</button>
                </div>

                <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1 ml-auto">
                    <button @click="view = 'tabel'"
                            :class="view === 'tabel' ? 'bg-white text-blue-900 shadow font-bold' : 'text-gray-500 hover:text-gray-800'"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                        </svg>
                        Tabel
                    </button>
                    <button @click="view = 'kalender'"
                            :class="view === 'kalender' ? 'bg-white text-blue-900 shadow font-bold' : 'text-gray-500 hover:text-gray-800'"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Kalender
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== VIEW KALENDER ===== --}}
        <div x-show="view === 'kalender'" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white border border-t-0 border-gray-200 rounded-b-2xl shadow-sm p-4 sm:p-6">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-extrabold text-gray-900" x-text="judulBulan()"></h2>
                <div class="flex gap-2">
                    <button @click="prevBulan()"
                            class="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-gray-700
                                   bg-gray-100 hover:bg-gray-200 rounded-xl transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Sebelum
                    </button>
                    <button @click="nextBulan()"
                            class="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-gray-700
                                   bg-gray-100 hover:bg-gray-200 rounded-xl transition active:scale-95">
                        Berikut
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Grid kalender: 7 kolom, header hardcode --}}
            <div class="grid grid-cols-7 border-l border-t border-gray-200 rounded-xl overflow-hidden">

                {{-- Header 7 hari (Warna Asli) --}}
                <div class="kalender-header-hari">SENIN</div>
                <div class="kalender-header-hari">SELASA</div>
                <div class="kalender-header-hari">RABU</div>
                <div class="kalender-header-hari">KAMIS</div>
                <div class="kalender-header-hari">JUMAT</div>
                <div class="kalender-header-weekend">SABTU</div>
                <div class="kalender-header-weekend">MINGGU</div>

                {{-- Sel kosong offset awal --}}
                <template x-for="n in offsetAwal()" :key="'e'+n">
                    <div class="kalender-cell border-r border-b border-gray-200 bg-gray-50/50"></div>
                </template>

                {{-- Sel tanggal --}}
                <template x-for="tgl in jumlahHari()" :key="'d'+tgl">
                    <div :class="isHariIni(tgl) ? 'bg-blue-50' : 'bg-white hover:bg-slate-50'"
                         class="kalender-cell border-r border-b border-gray-200 p-2 sm:p-2.5 flex flex-col gap-1.5 transition-colors">
                        <div class="flex justify-end">
                            {{-- Lingkaran tanggal hari ini warna asli biru tua --}}
                            <span :class="isHariIni(tgl)
                                    ? 'w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs sm:text-sm font-extrabold bg-blue-900 text-white shadow'
                                    : 'w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-semibold text-gray-500'"
                                  x-text="tgl"></span>
                        </div>
                        <template x-for="item in getAbsensi(tgl)" :key="item.id">
                            {{-- Badge absensi warna asli (solid green/red/gray) --}}
                            <div :class="{
                                    'bg-green-600 text-white': item.status === 'hadir',
                                    'bg-red-600 text-white':   item.status === 'tidak',
                                    'bg-gray-400 text-white':  !item.status
                                 }"
                                 class="rounded-lg px-2 py-1.5 text-[10px] sm:text-xs font-semibold shadow-sm flex flex-col leading-snug"
                                 :title="item.nama">
                                <span x-text="item.status === 'hadir' ? '✓ Hadir' : (item.status === 'tidak' ? '✗ Tidak Hadir' : '○ Belum Diabsen')"></span>
                                <span class="opacity-80 truncate mt-0.5" x-text="item.nama"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="flex flex-wrap gap-5 mt-5 pt-4 border-t border-gray-100">
                <div class="flex items-center gap-2 text-sm font-semibold text-gray-600">
                    <span class="w-4 h-4 rounded-md bg-green-600"></span> Hadir
                </div>
                <div class="flex items-center gap-2 text-sm font-semibold text-gray-600">
                    <span class="w-4 h-4 rounded-md bg-red-600"></span> Tidak Hadir
                </div>
                <div class="flex items-center gap-2 text-sm font-semibold text-gray-600">
                    <span class="w-4 h-4 rounded-md bg-gray-400"></span> Belum Diabsen
                </div>
            </div>
        </div>

        {{-- ===== VIEW TABEL ===== --}}
        <div x-show="view === 'tabel'" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white border border-t-0 border-gray-200 rounded-b-2xl shadow-sm overflow-hidden">

            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Menampilkan <strong class="text-gray-800" x-text="filteredRiwayat().length"></strong> sesi
                    <template x-if="filterStatus !== '' || search !== ''">
                        <span> (difilter)</span>
                    </template>
                </p>
                <button x-show="filterStatus !== '' || search !== ''"
                        @click="filterStatus = ''; search = ''"
                        class="text-xs font-semibold text-blue-900 hover:underline transition">Reset filter</button>
            </div>

            <div class="hidden md:block overflow-x-auto table-scroll">
                <table class="w-full text-left">
                    {{-- Kembalikan warna header biru tua seperti aslinya --}}
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider w-12">No</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider">Nama Sesi</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider">Waktu</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider">Status</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="filteredRiwayat().length === 0">
                            <tr>
                                <td colspan="6" class="py-16 text-center text-gray-400 text-sm">
                                    Tidak ada data yang cocok.
                                </td>
                            </tr>
                        </template>
                        <template x-for="(r, i) in filteredRiwayat()" :key="r.id">
                            {{-- Animasi list dengan delay dinamis --}}
                            <tr class="hover:bg-blue-50/40 transition-colors duration-200 animate-list-in" 
                                :style="`animation-delay: ${i * 50}ms`">
                                
                                <td class="px-5 py-4 text-sm text-gray-500 font-medium" x-text="i + 1"></td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-gray-800" x-text="r.nama_sesi"></div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700" x-text="r.tanggal_fmt"></td>
                                <td class="px-5 py-4 text-sm font-mono text-gray-700" x-text="r.waktu || '—'"></td>
                                <td class="px-5 py-4">
                                    <span :class="{
                                            'bg-green-100 text-green-800 border border-green-200': r.status === 'hadir',
                                            'bg-red-100 text-red-800 border border-red-200': r.status === 'tidak',
                                            'bg-gray-100 text-gray-600 border border-gray-200': !r.status
                                          }"
                                          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold shadow-sm">
                                        <span :class="{
                                                'bg-green-500': r.status === 'hadir',
                                                'bg-red-500': r.status === 'tidak',
                                                'bg-gray-400': !r.status
                                              }"
                                              class="w-2 h-2 rounded-full"></span>
                                        <span x-text="r.status === 'hadir' ? 'Hadir' : (r.status === 'tidak' ? 'Tidak Hadir' : 'Belum Diabsen')"></span>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600" x-text="r.keterangan || '—'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="block md:hidden divide-y divide-gray-100">
                <template x-if="filteredRiwayat().length === 0">
                    <div class="py-12 text-center text-gray-400 text-sm">Tidak ada data yang cocok.</div>
                </template>
                <template x-for="(r, i) in filteredRiwayat()" :key="r.id">
                    {{-- Animasi mobile --}}
                    <div class="p-4 hover:bg-blue-50/30 transition-colors animate-list-in"
                         :style="`animation-delay: ${i * 50}ms`">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-900 text-white flex items-center justify-center text-xs font-bold flex-shrink-0"
                                 x-text="i + 1"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-800" x-text="r.nama_sesi"></div>
                                <div class="text-xs text-gray-500 mt-0.5 flex flex-wrap gap-2">
                                    <span x-text="'📅 ' + r.tanggal_fmt"></span>
                                    <template x-if="r.waktu">
                                        <span x-text="'🕒 ' + r.waktu"></span>
                                    </template>
                                </div>
                                <div class="mt-2">
                                    <span :class="{
                                            'bg-green-100 text-green-800 border border-green-200': r.status === 'hadir',
                                            'bg-red-100 text-red-800 border border-red-200': r.status === 'tidak',
                                            'bg-gray-100 text-gray-600 border border-gray-200': !r.status
                                          }"
                                          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                        <span :class="{
                                                'bg-green-500': r.status === 'hadir',
                                                'bg-red-500': r.status === 'tidak',
                                                'bg-gray-400': !r.status
                                              }"
                                              class="w-2 h-2 rounded-full"></span>
                                        <span x-text="r.status === 'hadir' ? 'Hadir' : (r.status === 'tidak' ? 'Tidak Hadir' : 'Belum Diabsen')"></span>
                                    </span>
                                </div>
                                <template x-if="r.keterangan">
                                    <div class="mt-1.5 text-xs text-gray-500 bg-gray-50 rounded-lg p-2 border border-gray-100" x-text="r.keterangan"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function presensiApp() {
    const kalenderData = @json($kalenderData);
    const tabelData = @json($tabelData);

    return {
        view: 'tabel',
        search: '',
        filterStatus: '',

        filteredRiwayat() {
            return tabelData.filter(r => {
                const matchSearch = this.search === ''
                    || r.nama_sesi.toLowerCase().includes(this.search.toLowerCase());
                const matchStatus = this.filterStatus === ''
                    || (this.filterStatus === 'hadir'  && r.status === 'hadir')
                    || (this.filterStatus === 'tidak'  && r.status === 'tidak');
                return matchSearch && matchStatus;
            });
        },

        tahun: new Date().getFullYear(),
        bulan: new Date().getMonth(),

        judulBulan() {
            return new Date(this.tahun, this.bulan, 1)
                .toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        },
        jumlahHari() {
            return new Date(this.tahun, this.bulan + 1, 0).getDate();
        },
        offsetAwal() {
            const day = new Date(this.tahun, this.bulan, 1).getDay();
            return (day + 6) % 7;
        },
        isHariIni(tgl) {
            const t = new Date();
            return t.getDate() === tgl && t.getMonth() === this.bulan && t.getFullYear() === this.tahun;
        },
        getAbsensi(tgl) {
            const pad  = String(tgl).padStart(2, '0');
            const bln  = String(this.bulan + 1).padStart(2, '0');
            const key  = `${this.tahun}-${bln}-${pad}`;
            return kalenderData.filter(r => r.tgl === key);
        },
        prevBulan() {
            if (this.bulan === 0) { this.bulan = 11; this.tahun--; } else this.bulan--;
        },
        nextBulan() {
            if (this.bulan === 11) { this.bulan = 0; this.tahun++; } else this.bulan++;
        },
    };
}
</script>
@endpush