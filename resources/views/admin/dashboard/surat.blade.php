@extends('layouts.admin')

@section('title', 'Dashboard - Pengelola Surat')

@section('content')
<div class="space-y-6 bg-slate-50 min-h-screen pb-10 p-4 sm:p-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl shadow-lg p-6 sm:p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 pointer-events-none rotate-anim"></div>
        <div class="relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Dashboard Pengelola Surat</h1>
            <p class="text-sm sm:text-base text-amber-50 mt-1.5">Selamat datang, <strong>{{ Auth::user()->name }}</strong>! Berikut ringkasan surat yang dibuat.</p>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Hari Ini -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Surat Hari Ini</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $suratHariIni ?? 0 }}</p>
                </div>
                <div class="bg-amber-50 p-3 rounded-xl text-amber-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Minggu Ini -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Minggu Ini</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $suratMingguIni ?? 0 }}</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-xl text-blue-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
            </div>
        </div>

        <!-- Bulan Ini -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Bulan Ini</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $suratBulanIni ?? 0 }}</p>
                </div>
                <div class="bg-emerald-50 p-3 rounded-xl text-emerald-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Tahun Ini -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Tahun Ini</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $suratTahunIni ?? 0 }}</p>
                </div>
                <div class="bg-purple-50 p-3 rounded-xl text-purple-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel 5 Surat Terakhir -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">5 Surat Terakhir</h3>
            <a href="{{ route('admin.kelolasurat.index') }}" class="text-amber-600 hover:text-amber-700 text-sm font-bold transition-colors">Lihat Semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="text-left py-3 px-4 font-bold text-slate-600 rounded-tl-lg">No Surat</th>
                        <th class="text-left py-3 px-4 font-bold text-slate-600">Pengguna</th>
                        <th class="text-left py-3 px-4 font-bold text-slate-600">Jenis</th>
                        <th class="text-left py-3 px-4 font-bold text-slate-600">Tanggal</th>
                        <th class="text-center py-3 px-4 font-bold text-slate-600 rounded-tr-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suratTerakhir as $surat)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-amber-600">{{ $surat->no_surat }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs">
                                        {{ strtoupper(substr($surat->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-slate-900">{{ $surat->user->name ?? 'User' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $surat->jenis_surat ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $surat->tanggal ? $surat->tanggal->format('d M Y') : $surat->created_at->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('admin.kelolasurat.edit', $surat->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 font-medium">Belum ada surat yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Aksi Cepat -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Aksi Cepat</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.kelolasurat.index') }}" class="inline-flex items-center px-5 py-2.5 bg-amber-500 text-white font-bold rounded-xl hover:bg-amber-600 shadow-sm transition-all">
                Buka Kelola Surat
            </a>
            <a href="{{ route('admin.kelolasurat.export') }}" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 shadow-sm transition-all">
                Export Data
            </a>
        </div>
    </div>
</div>

<style>
    .rotate-anim { animation: spin-anim 15s linear infinite; }
    @keyframes spin-anim { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
@endsection