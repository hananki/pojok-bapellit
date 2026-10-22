@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    $isAdmin = $user?->isAdmin() ?? false;
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.user';
@endphp

@extends($layout)

@section('title', 'Booking Aula')

@section('content')

{{-- ========================================================= --}}
{{-- STYLE KHUSUS HALAMAN BOOKING AULA --}}
{{-- ========================================================= --}}
<style>
    /* Navbar transparan saat halaman booking */
    body:has(.booking-aula-page) nav,
    body:has(.booking-aula-page) header {
        position: absolute !important;
        top: 0 !important; left: 0 !important; right: 0 !important;
        width: 100% !important;
        z-index: 100 !important;
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }

    body:has(.booking-aula-page) nav,
    body:has(.booking-aula-page) nav a,
    body:has(.booking-aula-page) nav button,
    body:has(.booking-aula-page) nav span,
    body:has(.booking-aula-page) nav p,
    body:has(.booking-aula-page) nav .text-gray-400,
    body:has(.booking-aula-page) nav .text-gray-500,
    body:has(.booking-aula-page) nav .text-gray-600,
    body:has(.booking-aula-page) nav .text-gray-700,
    body:has(.booking-aula-page) nav .text-gray-800,
    body:has(.booking-aula-page) nav .text-gray-900,
    body:has(.booking-aula-page) nav .text-indigo-500,
    body:has(.booking-aula-page) nav .text-indigo-600,
    body:has(.booking-aula-page) nav .text-indigo-700 {
        color: #ffffff !important;
    }

    body:has(.booking-aula-page) nav a:hover,
    body:has(.booking-aula-page) nav button:hover {
        color: #ffffff !important;
    }

    body:has(.booking-aula-page) nav > div a { color: #ffffff !important; }
    body:has(.booking-aula-page) nav > div a:hover { color: #ffffff !important; }
    body:has(.booking-aula-page) nav > div { background: transparent !important; }

    body:has(.booking-aula-page) nav a.border-indigo-600,
    body:has(.booking-aula-page) nav a.text-indigo-600 {
        color: #ffffff !important;
        border-color: #ffffff !important;
    }

    body:has(.booking-aula-page) nav svg,
    body:has(.booking-aula-page) nav button svg,
    body:has(.booking-aula-page) nav a[href*="notification"] svg,
    body:has(.booking-aula-page) nav [aria-haspopup="true"] svg,
    body:has(.booking-aula-page) nav > div > button svg {
        color: #ffffff !important;
        stroke: #ffffff !important;
    }

    body:has(.booking-aula-page) nav button,
    body:has(.booking-aula-page) nav button span,
    body:has(.booking-aula-page) nav [aria-haspopup="true"],
    body:has(.booking-aula-page) nav [aria-haspopup="true"] span,
    body:has(.booking-aula-page) nav > div > button {
        color: #ffffff !important;
    }

    body:has(.booking-aula-page) nav .absolute,
    body:has(.booking-aula-page) nav .absolute a,
    body:has(.booking-aula-page) nav .absolute button,
    body:has(.booking-aula-page) nav .absolute span,
    body:has(.booking-aula-page) nav .absolute p {
        color: #1f2937 !important;
    }
    body:has(.booking-aula-page) nav .absolute a:hover,
    body:has(.booking-aula-page) nav .absolute button:hover {
        color: #4f46e5 !important;
    }
    body:has(.booking-aula-page) nav .absolute svg {
        color: #94a3b8 !important;
        stroke: #94a3b8 !important;
    }
    body:has(.booking-aula-page) nav .absolute .text-gray-900 { color: #111827 !important; }
    body:has(.booking-aula-page) nav .absolute .text-gray-800 { color: #1f2937 !important; }
    body:has(.booking-aula-page) nav .absolute .text-gray-700 { color: #374151 !important; }
    body:has(.booking-aula-page) nav .absolute .text-gray-600 { color: #4b5563 !important; }
    body:has(.booking-aula-page) nav .absolute .text-gray-500 { color: #6b7280 !important; }

    body:has(.booking-aula-page) nav .absolute .bg-red-500,
    body:has(.booking-aula-page) nav .absolute .bg-red-600 {
        color: #ffffff !important;
    }

    body:has(.booking-aula-page) nav .md\:hidden a { color: #1f2937 !important; }
    body:has(.booking-aula-page) nav .md\:hidden a:hover { color: #4f46e5 !important; }

    /* Dark Mode overrides untuk dropdown di booking aula */
    html.dark body:has(.booking-aula-page) nav .absolute,
    html.dark body:has(.booking-aula-page) nav .absolute a,
    html.dark body:has(.booking-aula-page) nav .absolute button,
    html.dark body:has(.booking-aula-page) nav .absolute span,
    html.dark body:has(.booking-aula-page) nav .absolute p {
        color: #f1f5f9 !important;
    }
    html.dark body:has(.booking-aula-page) nav .absolute a:hover,
    html.dark body:has(.booking-aula-page) nav .absolute button:hover {
        color: #2dd4bf !important;
    }
    html.dark body:has(.booking-aula-page) nav .absolute .text-gray-900,
    html.dark body:has(.booking-aula-page) nav .absolute .text-gray-800 { color: #ffffff !important; }
    html.dark body:has(.booking-aula-page) nav .absolute .text-gray-700,
    html.dark body:has(.booking-aula-page) nav .absolute .text-gray-600 { color: #cbd5e1 !important; }
    html.dark body:has(.booking-aula-page) nav .absolute .text-gray-500 { color: #94a3b8 !important; }
    html.dark body:has(.booking-aula-page) nav .md\:hidden a { color: #f1f5f9 !important; }

    body:has(.booking-aula-page) nav::after { display: none !important; }


    /* ===== ANIMASI MASUK CARD ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px) scale(0.96);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .booking-card {
        opacity: 0;
        animation: fadeInUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        will-change: transform;
    }

    /* ===== RIPPLE EFFECT ===== */
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(99, 102, 241, 0.35);
        transform: scale(0);
        animation: ripple 0.7s ease-out;
        pointer-events: none;
        z-index: 10;
    }
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    /* ===== BUTTON ANIMASI ===== */
    .booking-btn {
        position: relative;
        overflow: hidden;
    }
    .booking-btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        transform: translate(-50%, -50%);
        transition: width 0.5s ease, height 0.5s ease;
    }
    .booking-btn:hover::after {
        width: 300px;
        height: 300px;
    }

    /* ===== MINI CALENDAR ===== */
    .mini-calendar-cell {
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 600;
        border-radius: 6px;
        transition: background 0.15s ease;
        position: relative;
    }
    @media (min-width: 640px) {
        .mini-calendar-cell { font-size: 11px; }
    }
    .mini-calendar-cell.today {
        box-shadow: inset 0 0 0 2px #6366f1;
    }
    .mini-calendar-cell.booked {
        background: #ef4444;
        color: #ffffff;
        cursor: not-allowed;
    }
    .mini-calendar-cell.available {
        background: #f3f4f6;
        color: #374151;
        cursor: pointer;
    }
    .mini-calendar-cell.available:hover {
        background: #e5e7eb;
    }
    .mini-calendar-cell.past {
        color: #d1d5db;
        background: transparent;
        cursor: not-allowed;
    }

    /* ===== TOOLTIP KALENDER (Desktop) ===== */
    .cal-tooltip {
        position: absolute;
        bottom: calc(100% + 10px);
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #1f2937;
        color: #fff;
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 10px;
        line-height: 1.5;
        z-index: 50;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        font-weight: 500;
        min-width: 140px;
        max-width: 220px;
        text-align: left;
    }
    .cal-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #1f2937;
    }

    @media (hover: hover) and (pointer: fine) {
        .mini-calendar-cell:hover .cal-tooltip {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    /* Tooltip di tepi kiri */
    .mini-calendar-cell:nth-child(7n+1) .cal-tooltip,
    .mini-calendar-cell:nth-child(7n+2) .cal-tooltip {
        left: 0;
        transform: translateX(0) translateY(4px);
    }
    .mini-calendar-cell:nth-child(7n+1):hover .cal-tooltip,
    .mini-calendar-cell:nth-child(7n+2):hover .cal-tooltip {
        transform: translateX(0) translateY(0);
    }
    .mini-calendar-cell:nth-child(7n+1) .cal-tooltip::after,
    .mini-calendar-cell:nth-child(7n+2) .cal-tooltip::after {
        left: 20%;
    }

    /* Tooltip di tepi kanan */
    .mini-calendar-cell:nth-child(7n) .cal-tooltip,
    .mini-calendar-cell:nth-child(7n+6) .cal-tooltip {
        left: auto;
        right: 0;
        transform: translateX(0) translateY(4px);
    }
    .mini-calendar-cell:nth-child(7n):hover .cal-tooltip,
    .mini-calendar-cell:nth-child(7n+6):hover .cal-tooltip {
        transform: translateX(0) translateY(0);
    }
    .mini-calendar-cell:nth-child(7n) .cal-tooltip::after,
    .mini-calendar-cell:nth-child(7n+6) .cal-tooltip::after {
        left: auto;
        right: 20%;
        transform: translateX(50%);
    }

    /* ===== LIST BOOKING (SCROLL VERTIKAL) ===== */
    .booking-list-scroll {
        max-height: 160px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }
    .booking-list-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .booking-list-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }
    .booking-list-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    .booking-list-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Animasi pulse pelan */
    @keyframes pulseSlow {
        0%, 100% { opacity: 1; }
        50%      { opacity: 0.7; }
    }
    .animate-pulse-slow {
        animation: pulseSlow 2s ease-in-out infinite;
    }

    @media (hover: none) and (pointer: coarse) {
        .mini-calendar-cell {
            cursor: pointer !important;
        }
        .mini-calendar-cell.past {
            cursor: not-allowed !important;
        }
    }

    /* ===== COLLAPSE TRANSITION ===== */
    .collapse-content {
        transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.25s ease,
                    padding 0.3s ease;
        overflow: hidden;
    }
    .collapse-content.closed {
        max-height: 0 !important;
        opacity: 0;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    .collapse-chevron {
        transition: transform 0.3s ease;
    }
    .collapse-chevron.rotated {
        transform: rotate(180deg);
    }

    /* ===== POPUP MOBILE MODAL ===== */
    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    @keyframes modalPop {
        from { opacity: 0; transform: scale(0.9); }
        to   { opacity: 1; transform: scale(1); }
    }

    .mobile-cal-modal {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        max-width: 400px;
        width: 100%;
        max-height: 80vh;
        overflow-y: auto;
        animation: modalPop 0.25s ease-out;
    }
</style>


{{-- ========================================================= --}}
{{-- CONTENT --}}
{{-- ========================================================= --}}
<div class="relative z-10 min-h-screen">

    {{-- HEADER / JUDUL --}}
    <div class="text-center pt-20 md:pt-25 pb-10 px-6">
        <h1 class="mt-6 text-3xl sm:text-4xl md:text-5xl lg:text-[52px] tracking-tight leading-tight"
            style="font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
                   font-weight: 800;
                   color: #0a0a0a;
                   letter-spacing: -0.02em;
                   text-shadow: 0 2px 8px rgba(255,255,255,0.9), 0 4px 16px rgba(255,255,255,0.6);">
            Pilih Aula yang Ingin Dibooking
        </h1>
    </div>


    {{-- DAFTAR AULA --}}
    <div class="max-w-[1215px] mx-auto px-6 pb-24 pt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-start">

            @forelse($aulas as $aula)
                @php
                    $fotos     = is_array($aula->foto) ? $aula->foto : [];
                    $totalFoto = count($fotos);

                    $tanggalTerpakai  = $aula->tanggal_terpakai ?? [];
                    $totalTerpakai    = count($tanggalTerpakai);

                    $bookingMap = $aula->booking_map ?? [];

                    $bulanIni   = now()->month;
                    $tahunIni   = now()->year;
                    $namaBulanMini = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                    ];
                    $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulanIni, $tahunIni);
                    $hariPertama = date('w', strtotime("$tahunIni-$bulanIni-01"));
                    $today = date('Y-m-d');

                    $bookingList = $aula->booking_list ?? [];
                    $totalBooking = count($bookingList);

                    $bookingHariIni = \App\Models\Booking::where('aula_id', $aula->id)
                        ->whereDate('tanggal_booking', $today)
                        ->whereIn('status', ['pending', 'approved'])
                        ->count();
                @endphp

                {{-- CARD AULA --}}
                <div class="booking-card bg-white rounded-2xl overflow-hidden shadow-[0_10px_25px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_35px_rgba(0,0,0,0.2)] transition-shadow duration-300"
                     style="animation-delay: {{ $loop->index * 120 }}ms;">

                    {{-- FOTO AULA --}}
                    <div class="relative h-56 bg-gray-100 overflow-hidden group"
                         x-data="{ currentSlide: 0, totalSlides: {{ $totalFoto > 0 ? $totalFoto : 1 }} }">

                        <div class="absolute inset-0">
                            @if($totalFoto > 0)
                                @foreach($fotos as $index => $foto)
                                    <div x-show="currentSlide === {{ $index }}"
                                         x-transition:enter="transition ease-out duration-500"
                                         x-transition:enter-start="opacity-0 scale-105"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute inset-0">
                                        <img src="{{ asset('storage/' . $foto) }}"
                                             alt="{{ $aula->nama }}"
                                             class="card-image w-full h-full object-cover">
                                    </div>
                                @endforeach
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                    <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent pointer-events-none"></div>

                        {{-- STATUS + INFO HARI INI --}}
                        <div class="absolute top-4 right-4 z-20 flex flex-col items-end gap-1.5">
                            @if($aula->status_aktif)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/95 text-emerald-600 text-xs font-bold shadow-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Tersedia
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/95 text-red-600 text-xs font-bold shadow-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Tidak Tersedia
                                </span>
                            @endif

                            @if($bookingHariIni > 0)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500 text-white text-[10px] font-bold shadow-md animate-pulse-slow">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Hari ini terbooking
                                </span>
                            @endif
                        </div>

                        {{-- BADGE OKUPANSI --}}
                        @if($totalTerpakai > 0)
                            <div class="absolute top-4 left-4 z-20">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full bg-red-500/95 text-white text-[10px] font-bold shadow-md backdrop-blur-sm">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $totalTerpakai }} terpakai
                                </span>
                            </div>
                        @endif

                        @if($totalFoto > 1)
                            {{-- PREV --}}
                            <button type="button"
                                    @click.stop="currentSlide = (currentSlide - 1 + totalSlides) % totalSlides"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            {{-- NEXT --}}
                            <button type="button"
                                    @click.stop="currentSlide = (currentSlide + 1) % totalSlides"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            {{-- DOT INDICATOR --}}
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5">
                                @for($i = 0; $i < $totalFoto; $i++)
                                    <button type="button"
                                            @click.stop="currentSlide = {{ $i }}"
                                            class="h-1.5 rounded-full transition-all duration-300"
                                            :class="currentSlide === {{ $i }} ? 'bg-white w-5' : 'bg-white/60 w-1.5'"></button>
                                @endfor
                            </div>
                        @endif
                    </div>


                    {{-- INFORMASI AULA --}}
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $aula->nama }}</h3>

                        {{-- KAPASITAS --}}
                        <div class="mb-4">
                            <span class="inline-flex items-center gap-1.5 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Kapasitas:
                                <span class="font-semibold text-gray-800">{{ $aula->kapasitas }}</span>
                                Orang
                            </span>
                        </div>

                        {{-- ===================================================== --}}
                        {{-- MINI CALENDAR + LIST BOOKING (COLLAPSIBLE) --}}
                        {{-- ===================================================== --}}
                        <div class="mb-4 rounded-xl bg-gradient-to-br from-gray-50 to-slate-50 border border-gray-100 overflow-hidden"
                             x-data="collapsePanel()">

                            {{-- HEADER TOGGLE --}}
                            <button type="button"
                                    @click="toggle()"
                                    class="w-full flex items-center justify-between gap-2 p-3.5 hover:bg-gray-100/50 transition-colors">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wide truncate">
                                        Ketersediaan {{ $namaBulanMini[$bulanIni] }} {{ $tahunIni }}
                                    </span>
                                    @php
                                        if ($totalTerpakai >= 15) {
                                            $badgeClass = 'text-red-700 bg-red-100';
                                        } elseif ($totalTerpakai >= 7) {
                                            $badgeClass = 'text-amber-700 bg-amber-100';
                                        } else {
                                            $badgeClass = 'text-emerald-700 bg-emerald-100';
                                        }
                                    @endphp
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap {{ $badgeClass }}">
                                        {{ $totalTerpakai }} terisi
                                    </span>
                                </div>
                                <svg class="collapse-chevron w-4 h-4 text-gray-400 flex-shrink-0"
                                     :class="open ? 'rotated' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            {{-- CONTENT (COLLAPSIBLE) --}}
                            <div class="collapse-content px-3.5 pb-3.5"
                                 :class="open ? '' : 'closed'"
                                 x-ref="content"
                                 style="max-height: 2000px;">
                                {{-- Header hari --}}
                                <div class="grid grid-cols-7 gap-0.5 text-center text-[9px] font-bold text-gray-400 mb-1">
                                    <div>M</div><div>S</div><div>S</div><div>R</div><div>K</div><div>J</div><div>S</div>
                                </div>

                                {{-- Grid tanggal --}}
                                <div class="grid grid-cols-7 gap-0.5">
                                    @for($i = 0; $i < $hariPertama; $i++)
                                        <div></div>
                                    @endfor

                                    @for($day = 1; $day <= $jumlahHari; $day++)
                                        @php
                                            $dateString = sprintf('%04d-%02d-%02d', $tahunIni, $bulanIni, $day);
                                            $isToday    = $dateString === $today;
                                            $isBooked   = in_array($dateString, $tanggalTerpakai);
                                            $isPast     = $dateString < $today;

                                            $bookingsHariIni = $bookingMap[$dateString] ?? [];
                                            $jumlahBookingHariIni = count($bookingsHariIni);

                                            $tanggalDisplay = $day . ' ' . $namaBulanMini[$bulanIni] . ' ' . $tahunIni;
                                        @endphp
                                        <div class="mini-calendar-cell
                                            {{ $isPast ? 'past' : '' }}
                                            {{ $isBooked ? 'booked' : '' }}
                                            {{ !$isPast && !$isBooked ? 'available' : '' }}
                                            {{ $isToday ? 'today' : '' }}"
                                            @if(!$isPast)
                                                onclick='showCalTooltip(event, "{{ $tanggalDisplay }}", {{ $jumlahBookingHariIni }}, @json($bookingsHariIni))'
                                            @endif
                                            >
                                            {{ $day }}

                                            @if(!$isPast)
                                                <div class="cal-tooltip">
                                                    @if($jumlahBookingHariIni > 0)
                                                        <div class="font-bold text-amber-400 mb-1.5 flex items-center gap-1">
                                                            <span>📌</span>
                                                            <span>{{ $jumlahBookingHariIni }} booking — {{ $tanggalDisplay }}</span>
                                                        </div>
                                                        @foreach($bookingsHariIni as $b)
                                                            <div class="mb-1.5 last:mb-0 pb-1.5 last:pb-0 border-b border-white/10 last:border-0">
                                                                <div class="flex items-center gap-1.5">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                                                                    <span class="text-white font-semibold truncate max-w-[130px]" title="{{ $b['nama'] }}">{{ $b['nama'] }}</span>
                                                                    <span class="text-gray-500">·</span>
                                                                    <span class="text-indigo-300 whitespace-nowrap text-[10px]">Sesi {{ $b['sesi'] }}</span>
                                                                </div>
                                                                <div class="text-gray-300 text-[10px] leading-snug mt-0.5 pl-3 line-clamp-2" title="{{ $b['keperluan'] }}">
                                                                    <span class="text-gray-500">Keperluan:</span> {{ $b['keperluan'] }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="font-bold text-emerald-400 flex items-center gap-1">
                                                            <span>✓</span>
                                                            <span>Tersedia — {{ $tanggalDisplay }}</span>
                                                        </div>
                                                        <div class="text-gray-400 text-[9px] mt-0.5">Belum ada yang booking</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                </div>

                                {{-- Legend --}}
                                <div class="flex items-center justify-center gap-3 mt-2.5 text-[9px] text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-sm bg-red-500"></span>
                                        <span>Terbooking</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-sm bg-gray-200"></span>
                                        <span>Tersedia</span>
                                    </div>
                                </div>

                                {{-- LIST BOOKING --}}
                                @if($totalBooking > 0)
                                    <div class="mt-2.5 pt-2.5 border-t border-gray-200/70">
                                        <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Booking Terbaru
                                            </span>
                                            <span class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded-full">
                                                {{ $totalBooking }}
                                            </span>
                                        </p>

                                        <div class="booking-list-scroll space-y-1.5 pr-1">
                                            @foreach($bookingList as $b)
                                                <div class="flex items-center gap-2 p-2 rounded-lg bg-white border border-indigo-100 shadow-sm">
                                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-indigo-50 flex flex-col items-center justify-center">
                                                        <span class="text-[9px] font-bold text-indigo-500 uppercase leading-none">
                                                            {{ explode(' ', $b['tanggal'])[1] ?? '' }}
                                                        </span>
                                                        <span class="text-sm font-extrabold text-indigo-700 leading-tight">
                                                            {{ explode(' ', $b['tanggal'])[0] ?? '-' }}
                                                        </span>
                                                    </div>

                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[11px] font-bold text-gray-800 truncate" title="{{ $b['nama'] }}">
                                                            {{ $b['nama'] }}
                                                        </p>
                                                        <p class="text-[10px] text-gray-500 flex items-center gap-1 mt-0.5">
                                                            <svg class="w-2.5 h-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Sesi {{ $b['sesi'] }}
                                                        </p>
                                                        <p class="text-[10px] leading-snug text-gray-600 mt-0.5 truncate" title="{{ $b['keperluan'] }}">
                                                            <span class="text-gray-400">Keperluan:</span> {{ $b['keperluan'] }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-2.5 pt-2.5 border-t border-gray-200/70">
                                        <p class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Belum ada booking
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- BUTTON BOOKING (SELALU DI BAWAH) --}}
                        @if($aula->status_aktif)
                            <a href="{{ route('booking.show', $aula->id) }}"
                               class="booking-btn w-full h-12 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center text-sm font-semibold transition shadow-sm hover:shadow-md relative z-20">
                                <span class="relative z-10">Lihat Ketersediaan & Booking</span>
                            </a>
                        @else
                            <button type="button" disabled
                                    class="w-full h-12 rounded-xl bg-gray-200 text-gray-500 text-sm font-semibold cursor-not-allowed">
                                Tidak Tersedia
                            </button>
                        @endif
                    </div>
                </div>

            @empty

                {{-- TIDAK ADA AULA --}}
                <div class="col-span-full">
                    <div class="bg-white/95 rounded-2xl p-12 text-center shadow-xl">
                        <svg class="w-24 h-24 text-gray-300 mx-auto mb-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011 1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>

                        <h3 class="text-2xl font-bold text-gray-700 mb-2">Belum Ada Aula Tersedia</h3>
                        <p class="text-gray-500 text-sm mb-6">Silahkan hubungi admin untuk menambahkan aula.</p>

                        @if($isAdmin)
                            <a href="{{ route('admin.aula.create') }}"
                               class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">
                                Tambah Aula Sekarang
                            </a>
                        @endif
                    </div>
                </div>

            @endforelse

        </div>
    </div>
</div>


{{-- ========================================================= --}}
{{-- SCRIPT ===== --}}
{{-- ========================================================= --}}
<script>
    // ===== COLLAPSE PANEL (Alpine component) =====
    function collapsePanel() {
        return {
            open: true,
            toggle() {
                this.open = !this.open;

                const content = this.$refs.content;
                if (!content) return;

                if (this.open) {
                    content.classList.remove('closed');
                    content.style.maxHeight = content.scrollHeight + 'px';
                } else {
                    content.style.maxHeight = content.scrollHeight + 'px';
                    content.offsetHeight;
                    content.classList.add('closed');
                }
            }
        }
    }

    // ===== POPUP KALENDER (Mobile Modal) =====
    window.showCalTooltip = function(event, tanggalDisplay, jumlah, bookings) {
        event.stopPropagation();

        const isTouch = window.matchMedia('(hover: none) and (pointer: coarse)').matches;
        if (!isTouch) return;

        // Hapus modal lama kalau ada
        document.querySelectorAll('.mobile-cal-overlay').forEach(el => el.remove());

        // Buat overlay + modal
        const overlay = document.createElement('div');
        overlay.className = 'mobile-cal-overlay';

        let bodyHtml = '';
        if (jumlah > 0) {
            bodyHtml = `<div class="text-sm font-bold text-amber-600 mb-2 flex items-center gap-1.5">📌 ${jumlah} booking</div>`;
            bookings.forEach(b => {
                const keperluan = b.keperluan || '-';
                bodyHtml += `
                    <div class="py-2.5 border-b border-gray-100 last:border-0">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></div>
                            <span class="text-sm text-gray-800 font-semibold truncate flex-1">${b.nama}</span>
                            <span class="text-xs text-indigo-600 font-bold whitespace-nowrap">Sesi ${b.sesi}</span>
                        </div>
                        <div class="text-xs text-gray-600 mt-1 pl-4 leading-snug">
                            <span class="text-gray-400">Keperluan:</span> ${keperluan}
                        </div>
                    </div>
                `;
            });
        } else {
            bodyHtml = `
                <div class="flex items-center gap-2 text-emerald-600 py-2">
                    <span class="text-lg">✓</span>
                    <span class="text-sm font-bold">Tersedia</span>
                </div>
                <div class="text-xs text-gray-500 mt-1">Belum ada yang booking</div>
            `;
        }

        overlay.innerHTML = `
            <div class="mobile-cal-modal">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                    <span class="text-base font-bold text-gray-800">${tanggalDisplay}</span>
                    <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl leading-none -mt-1" onclick="this.closest('.mobile-cal-overlay').remove()">&times;</button>
                </div>
                <div>${bodyHtml}</div>
            </div>
        `;

        overlay.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeInOverlay 0.2s ease-out;
        `;

        document.body.appendChild(overlay);

        // Klik overlay untuk tutup
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.remove();
        });
    };

    // ===== RIPPLE EFFECT (hanya di tombol booking) =====
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.booking-btn');

        buttons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                createRipple(btn, e);
            });
        });

        function createRipple(container, event) {
            const rect = container.getBoundingClientRect();

            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');

            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (x - size / 2) + 'px';
            ripple.style.top = (y - size / 2) + 'px';

            if (getComputedStyle(container).position === 'static') {
                container.style.position = 'relative';
            }
            container.style.overflow = 'hidden';

            container.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 700);
        }
    });
</script>

@endsection