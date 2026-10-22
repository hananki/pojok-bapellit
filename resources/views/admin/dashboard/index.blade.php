@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="space-y-6 bg-slate-50 min-h-screen pb-10">

    <!-- Header Card (Warna Navbar) -->
    <div class="bg-gradient-to-r from-green-500 via-teal-600 to-blue-600 rounded-2xl shadow-lg p-6 sm:p-8 overflow-hidden relative">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 pointer-events-none rotate-anim"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 rounded-full bg-black/10 pointer-events-none rotate-anim-reverse"></div>
        
        <div class="relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight drop-shadow-sm">Dashboard Admin</h1>
            <p class="text-sm sm:text-base text-teal-50 mt-1.5 leading-relaxed">
                Selamat datang kembali, <strong>{{ Auth::user()->name }}</strong>! Berikut adalah ringkasan aktivitas sistem hari ini.
            </p>
        </div>
    </div>

    <!-- Grid Statistics Cards (Top) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- Users Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Pengguna</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $totalUsers }}</p>
                    <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-green-600 font-bold">{{ $activePegawai }}</span> aktif
                    </p>
                </div>
                <div class="bg-blue-50 p-3 rounded-xl text-blue-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Bookings Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Booking</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $totalBookings }}</p>
                    <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-amber-600 font-bold">{{ $pendingBookings }}</span> menunggu
                    </p>
                </div>
                <div class="bg-amber-50 p-3 rounded-xl text-amber-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Kendaraan Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Kendaraan</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $totalKendaraan }}</p>
                    <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span class="text-red-600 font-bold">{{ $pajakMendesak }}</span> perlu pajak
                    </p>
                </div>
                <div class="bg-indigo-50 p-3 rounded-xl text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 104 0m-4 0a2 2 0 104 0m0 0h2a2 2 0 002-2v-4a2 2 0 00-2-2H9m2 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2h2m0 0H7m2 0h2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Aula Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Aula</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $totalAula }}</p>
                    <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-emerald-600 font-bold">{{ $activeAula }}</span> aktif
                    </p>
                </div>
                <div class="bg-purple-50 p-3 rounded-xl text-purple-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Absensi Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Absensi</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $totalAbsensi }}</p>
                    <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                        <span class="text-teal-600 font-bold">{{ $hadir }}</span> hadir
                    </p>
                </div>
                <div class="bg-teal-50 p-3 rounded-xl text-teal-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Statistics (Middle) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Booking Status Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col h-full">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Status Booking
            </h3>
            <div class="space-y-3 flex-1">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Pending</span>
                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold">{{ $pendingBookings }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Approved</span>
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold">{{ $approvedBookings }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Completed</span>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold">{{ $completedBookings }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Rejected</span>
                    <span class="px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg text-xs font-bold">{{ $rejectedBookings }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Cancelled</span>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold">{{ $cancelledBookings }}</span>
                </div>
            </div>
        </div>

        <!-- Absensi Status Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col h-full">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Status Absensi
            </h3>
            <div class="space-y-3 flex-1">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Hadir</span>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold">{{ $hadir }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Tidak Hadir</span>
                    <span class="px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg text-xs font-bold">{{ $tidak }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Belum Diabsen</span>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold">{{ $belumAbsen }}</span>
                </div>
            </div>
        </div>

        <!-- Account Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col h-full">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Ringkasan Akun
            </h3>
            <div class="space-y-3 flex-1">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Admin</span>
                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold">{{ $totalAdmins }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Pegawai Aktif</span>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold">{{ $activePegawai }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Akun Nonaktif</span>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold">{{ $inactiveAccounts }}</span>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 mt-auto">
                    <span class="text-sm font-bold text-slate-800">Total</span>
                    <span class="px-2.5 py-1 bg-slate-800 text-white rounded-lg text-xs font-bold">{{ $totalUsers }}</span>
                </div>
            </div>
        </div>

        <!-- Pajak Kendaraan Mendesak (Maks 5) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Pajak Kendaraan
                </h3>
                <a href="{{ route('admin.kendaraan.index') }}" class="text-teal-600 hover:text-teal-700 text-xs font-bold transition-colors">
                    Lihat Semua →
                </a>
            </div>
            <div class="space-y-3 flex-1 overflow-y-auto pr-1" style="max-height: 250px;">
                @forelse($kendaraanMendesakList ?? [] as $pajak)
                    @php
                        $today = strtotime(date('Y-m-d'));
                        $jatuhTempo = strtotime(\Carbon\Carbon::parse($pajak->tanggal_jatuh_tempo)->format('Y-m-d'));
                        $hariTersisa = round(($jatuhTempo - $today) / 86400);
                        
                        if ($hariTersisa < 0) {
                            $borderColor = 'border-l-red-500'; 
                            $badgeColor = 'bg-red-100 text-red-700'; 
                            $statusTeks = 'Lewat ' . abs($hariTersisa) . ' hr';
                        } elseif ($hariTersisa == 0) {
                            $borderColor = 'border-l-red-500'; 
                            $badgeColor = 'bg-red-100 text-red-700'; 
                            $statusTeks = 'Hari ini';
                        } elseif ($hariTersisa <= 7) {
                            $borderColor = 'border-l-amber-500'; 
                            $badgeColor = 'bg-amber-100 text-amber-700'; 
                            $statusTeks = $hariTersisa . ' hr lagi';
                        } else {
                            $borderColor = 'border-l-blue-500'; 
                            $badgeColor = 'bg-blue-100 text-blue-700'; 
                            $statusTeks = $hariTersisa . ' hr lagi';
                        }

                        $namaPemakai = $pajak->kendaraan->pemakai ?? $pajak->kendaraan->user->name ?? 'Tanpa Nama';
                    @endphp

                    <div class="flex flex-col p-3 bg-slate-50 rounded-r-lg rounded-l-sm border-l-4 {{ $borderColor }} border-y border-r border-slate-100 hover:shadow-sm transition-all group">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-bold text-slate-900 group-hover:text-teal-700 transition-colors">
                                {{ $pajak->kendaraan->plat_nomor ?? 'N/A' }}
                            </span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $badgeColor }}">
                                {{ $statusTeks }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-xs text-slate-600 font-medium truncate flex items-center gap-1">
                                👤 {{ $namaPemakai }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-400 bg-white px-1.5 py-0.5 rounded border border-slate-100">
                                {{ \Carbon\Carbon::parse($pajak->tanggal_jatuh_tempo)->format('d/m/y') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-6 text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <svg class="w-8 h-8 mb-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs font-medium">Semua pajak aman</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom Section: Recent & Top -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Recent Bookings -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800">Booking Terbaru</h3>
                <a href="{{ route('admin.kelolabooking.index') }}" class="text-teal-600 hover:text-teal-700 text-sm font-bold transition-colors">
                    Lihat Semua →
                </a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="text-left py-3 px-4 font-bold text-slate-600 rounded-tl-lg">Pengguna</th>
                            <th class="text-left py-3 px-4 font-bold text-slate-600">Aula</th>
                            <th class="text-left py-3 px-4 font-bold text-slate-600 rounded-tr-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentBookings as $booking)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4 text-slate-900 font-medium">{{ $booking->user->name }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $booking->aula->nama ?? 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-md border {{ $statusBadgeClasses($booking->status) }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 px-4 text-center text-slate-500 font-medium">Tidak ada booking terbaru</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Aulas -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800">Aula Paling Sering Dibook</h3>
                <a href="{{ route('admin.aula.index') }}" class="text-teal-600 hover:text-teal-700 text-sm font-bold transition-colors">
                    Lihat Semua →
                </a>
            </div>
            <div class="space-y-3 flex-1">
                @forelse($topAula as $index => $aula)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 hover:bg-slate-100 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-sm font-bold group-hover:bg-teal-600 group-hover:text-white transition-colors">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $aula->nama }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">Kapasitas: {{ $aula->kapasitas }} orang</p>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold">
                            {{ $aula->bookings_count }} booking
                        </span>
                    </div>
                @empty
                    <p class="text-center text-slate-500 py-6 font-medium">Tidak ada aula</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Booking Trend Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-base font-bold text-slate-800 mb-4">Booking Trend (12 Bulan)</h3>
            <div class="relative w-full" style="height: 280px;">
                <canvas id="bookingTrendChart"></canvas>
            </div>
        </div>

        <!-- Booking Status Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-base font-bold text-slate-800 mb-4">Distribusi Status Booking</h3>
            <div class="relative w-full flex justify-center" style="height: 280px;">
                <canvas id="bookingStatusChart"></canvas>
            </div>
        </div>

        <!-- Absensi Distribution Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-base font-bold text-slate-800 mb-4">Status Absensi</h3>
            <div class="relative w-full flex justify-center" style="height: 280px;">
                <canvas id="absensiChart"></canvas>
            </div>
        </div>

        <!-- User Distribution Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-base font-bold text-slate-800 mb-4">Distribusi Pengguna</h3>
            <div class="relative w-full" style="height: 280px;">
                <canvas id="userChart"></canvas>
            </div>
        </div>

        <!-- Kendaraan Chart (Full Width) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 lg:col-span-2">
            <h3 class="text-base font-bold text-slate-800 mb-4">Status Pajak Kendaraan</h3>
            <div class="relative w-full" style="height: 250px;">
                <canvas id="kendaraanChart"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
    // Warna Tema (Teal, Emerald, Blue, Amber, Red)
    const colors = {
        primary: '#0d9488', // Teal
        success: '#10b981', // Emerald
        warning: '#f59e0b', // Amber
        danger: '#ef4444',  // Red
        info: '#3b82f6',    // Blue
        secondary: '#8b5cf6' // Purple
    };

    // Setelan Global Chart agar font dan warnanya rapi
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#64748b'; 
    Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.04)';
    Chart.defaults.scale.grid.borderColor = 'transparent';

    // ===== BOOKING TREND CHART =====
    const bookingTrendCtx = document.getElementById('bookingTrendChart').getContext('2d');
    new Chart(bookingTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($bookingTrend)) !!},
            datasets: [{
                label: 'Booking',
                data: {!! json_encode(array_values($bookingTrend)) !!},
                borderColor: colors.primary,
                backgroundColor: 'rgba(13, 148, 136, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 13 },
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // ===== BOOKING STATUS CHART =====
    const bookingStatusCtx = document.getElementById('bookingStatusChart').getContext('2d');
    new Chart(bookingStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Completed', 'Rejected', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $pendingBookings ?? 0 }},
                    {{ $approvedBookings ?? 0 }},
                    {{ $completedBookings ?? 0 }},
                    {{ $rejectedBookings ?? 0 }},
                    {{ $cancelledBookings ?? 0 }}
                ],
                backgroundColor: [colors.warning, colors.info, colors.success, colors.danger, '#94a3b8'],
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, usePointStyle: true, font: { size: 11, weight: 'bold' } }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 13 }
                }
            }
        }
    });

    // ===== ABSENSI DISTRIBUTION CHART =====
    const absensiCtx = document.getElementById('absensiChart').getContext('2d');
    new Chart(absensiCtx, {
        type: 'pie',
        data: {
            labels: ['Hadir', 'Tidak Hadir', 'Belum Diabsen'],
            datasets: [{
                data: [{{ $hadir ?? 0 }}, {{ $tidak ?? 0 }}, {{ $belumAbsen ?? 0 }}],
                backgroundColor: [colors.success, colors.danger, '#94a3b8'],
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, usePointStyle: true, font: { size: 11, weight: 'bold' } }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 13 }
                }
            }
        }
    });

    // ===== USER DISTRIBUTION CHART =====
    const userCtx = document.getElementById('userChart').getContext('2d');
    new Chart(userCtx, {
        type: 'bar',
        data: {
            labels: ['Admin', 'Pegawai Aktif', 'Nonaktif'],
            datasets: [{
                label: 'Jumlah',
                data: [{{ $totalAdmins ?? 0 }}, {{ $activePegawai ?? 0 }}, {{ $inactiveAccounts ?? 0 }}],
                backgroundColor: [colors.primary, colors.success, colors.warning],
                borderRadius: 6,
                barThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: 'bold' } }
                }
            }
        }
    });

    // ===== KENDARAAN CHART =====
    const kendaraanCtx = document.getElementById('kendaraanChart').getContext('2d');
    new Chart(kendaraanCtx, {
        type: 'bar',
        data: {
            labels: ['Total Kendaraan', 'Pajak Mendesak (Perlu Diurus)'],
            datasets: [{
                label: 'Jumlah',
                data: [{{ $totalKendaraan ?? 0 }}, {{ $pajakMendesak ?? 0 }}],
                backgroundColor: [colors.info, colors.danger],
                borderRadius: 6,
                barThickness: 60
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: 'bold' } }
                }
            }
        }
    });
</script>

<style>
    .rotate-anim {
        animation: spin-anim 15s linear infinite;
    }
    .rotate-anim-reverse {
        animation: spin-anim-reverse 20s linear infinite;
    }
    @keyframes spin-anim {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    @keyframes spin-anim-reverse {
        0% { transform: rotate(360deg); }
        100% { transform: rotate(0deg); }
    }
</style>
@endsection