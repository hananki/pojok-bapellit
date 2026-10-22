@extends('layouts.admin')

@section('title', 'Dashboard - Pengelola Absensi')

@section('content')
<div class="space-y-6 bg-slate-50 min-h-screen pb-10 p-4 sm:p-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-pink-500 to-rose-600 rounded-2xl shadow-lg p-6 sm:p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 pointer-events-none rotate-anim"></div>
        <div class="relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Dashboard Pengelola Absensi</h1>
            <p class="text-sm sm:text-base text-pink-50 mt-1.5">Selamat datang, <strong>{{ Auth::user()->name }}</strong>! Berikut ringkasan data kehadiran.</p>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Sesi -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Sesi</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $totalAbsensiSesi ?? 0 }}</p>
                </div>
                <div class="bg-pink-50 p-3 rounded-xl text-pink-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Hadir -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Hadir</p>
                    <p class="text-3xl font-extrabold text-emerald-600">{{ $hadir ?? 0 }}</p>
                </div>
                <div class="bg-emerald-50 p-3 rounded-xl text-emerald-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Tidak Hadir -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Tidak Hadir</p>
                    <p class="text-3xl font-extrabold text-red-600">{{ $tidak ?? 0 }}</p>
                </div>
                <div class="bg-red-50 p-3 rounded-xl text-red-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Belum Absen -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Belum Absen</p>
                    <p class="text-3xl font-extrabold text-amber-500">{{ $belumAbsen ?? 0 }}</p>
                </div>
                <div class="bg-amber-50 p-3 rounded-xl text-amber-500 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Grafik Batang (Bar Chart) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Perbandingan Kehadiran (7 Hari)
            </h3>
            <div class="relative w-full" style="height: 280px;">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <!-- Diagram Lingkaran (Doughnut) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                Distribusi Total Kehadiran
            </h3>
            <div class="relative w-full flex justify-center" style="height: 280px;">
                <canvas id="distribusiChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Tabel & Aksi Cepat -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Pegawai Belum Absen Hari Ini -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Belum Absen Hari Ini
                </h3>
                <a href="{{ route('admin.absensi.index') }}" class="text-pink-600 hover:text-pink-700 text-sm font-bold transition-colors">Kelola &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="text-left py-3 px-4 font-bold text-slate-600 rounded-tl-lg">Nama Pegawai</th>
                            <th class="text-left py-3 px-4 font-bold text-slate-600">Bidang</th>
                            <th class="text-center py-3 px-4 font-bold text-slate-600 rounded-tr-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pegawaiBelumAbsen as $pegawai)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs">
                                            {{ strtoupper(substr($pegawai->name, 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-slate-900">{{ $pegawai->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $pegawai->bidang ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-amber-50 text-amber-700 border border-amber-200">Belum Absen</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-500 font-medium">Semua pegawai sudah absen hari ini! 🎉</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Aksi Cepat -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 mb-4">Aksi Cepat</h3>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed">Butuh membuat sesi absensi baru atau mengelola data kehadiran? Klik tombol di bawah ini.</p>
            </div>
            <a href="{{ route('admin.absensi.index') }}" class="w-full inline-flex justify-center items-center px-5 py-3.5 bg-gradient-to-r from-pink-600 to-rose-600 text-white font-bold rounded-xl hover:from-pink-700 hover:to-rose-700 shadow-md transition-all">
                Kelola Absensi
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
    // Setelan Global Chart
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.04)';
    Chart.defaults.scale.grid.borderColor = 'transparent';

    // ===== GRAFIK BATANG (HADIR vs TIDAK HADIR) =====
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labelTanggal ?? []) !!},
            datasets: [
                {
                    label: 'Hadir',
                    data: {!! json_encode($trenKehadiran ?? []) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Tidak Hadir',
                    data: {!! json_encode($trenTidakHadir ?? []) !!},
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { usePointStyle: true, boxWidth: 8, font: { size: 11, weight: 'bold' } }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 13 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    stacked: false
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // ===== DIAGRAM LINGKARAN =====
    const distribusiCtx = document.getElementById('distribusiChart').getContext('2d');
    new Chart(distribusiCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Tidak Hadir', 'Belum Absen'],
            datasets: [{
                data: [{{ $hadir ?? 0 }}, {{ $tidak ?? 0 }}, {{ $belumAbsen ?? 0 }}],
                backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
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
                    labels: { padding: 20, usePointStyle: true, font: { size: 12, weight: 'bold' } }
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
</script>

<style>
    .rotate-anim { animation: spin-anim 15s linear infinite; }
    @keyframes spin-anim { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
@endsection