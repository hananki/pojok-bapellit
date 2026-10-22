@extends('layouts.admin')

@section('title', 'Dashboard - Pengelola Aula')

@section('content')
<div class="space-y-6 bg-slate-50 min-h-screen pb-10 p-4 sm:p-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-indigo-500 to-blue-600 rounded-2xl shadow-lg p-6 sm:p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 pointer-events-none rotate-anim"></div>
        <div class="relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Dashboard Pengelola Aula</h1>
            <p class="text-sm sm:text-base text-indigo-50 mt-1.5">Selamat datang, <strong>{{ Auth::user()->name }}</strong>! Berikut ringkasan booking dan penggunaan aula.</p>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Booking -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Booking</p>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $totalBookings ?? 0 }}</p>
                </div>
                <div class="bg-indigo-50 p-3 rounded-xl text-indigo-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Menunggu Approval -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Menunggu</p>
                    <p class="text-3xl font-extrabold text-amber-600">{{ $pendingBookings ?? 0 }}</p>
                </div>
                <div class="bg-amber-50 p-3 rounded-xl text-amber-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Disetujui -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Disetujui</p>
                    <p class="text-3xl font-extrabold text-blue-600">{{ $approvedBookings ?? 0 }}</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-xl text-blue-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:-translate-y-1 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Selesai</p>
                    <p class="text-3xl font-extrabold text-emerald-600">{{ $completedBookings ?? 0 }}</p>
                </div>
                <div class="bg-emerald-50 p-3 rounded-xl text-emerald-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Bawah: Top Aula & Booking Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Top Aula -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800">Aula Paling Sering Dibook</h3>
                <a href="{{ route('admin.aula.index') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-bold transition-colors">Lihat Semua &rarr;</a>
            </div>
            <div class="space-y-3">
                @forelse($topAula as $index => $aula)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 hover:bg-slate-100 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold group-hover:bg-indigo-600 group-hover:text-white transition-colors">
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
                    <p class="text-center text-slate-500 py-6 font-medium">Belum ada data aula.</p>
                @endforelse
            </div>
        </div>

        <!-- Booking Terbaru -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800">Booking Terbaru</h3>
                <a href="{{ route('admin.kelolabooking.index') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-bold transition-colors">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="text-left py-3 px-4 font-bold text-slate-600 rounded-tl-lg">Pengguna</th>
                            <th class="text-left py-3 px-4 font-bold text-slate-600">Aula</th>
                            <th class="text-center py-3 px-4 font-bold text-slate-600 rounded-tr-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentBookings as $booking)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs">
                                            {{ strtoupper(substr($booking->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-slate-900">{{ $booking->user->name ?? 'User' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $booking->aula->nama ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($booking->status === 'pending')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                    @elseif($booking->status === 'approved')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-blue-50 text-blue-700 border border-blue-200">Approved</span>
                                    @elseif($booking->status === 'completed')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">Selesai</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-slate-100 text-slate-700 border border-slate-200">{{ ucfirst($booking->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-500 font-medium">Belum ada booking terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .rotate-anim { animation: spin-anim 15s linear infinite; }
    @keyframes spin-anim { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
@endsection