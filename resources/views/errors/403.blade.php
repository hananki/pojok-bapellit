@extends('layouts.admin') {{-- Atau sesuaikan dengan layout utama Anda --}}

@section('title', 'Akses Ditolak')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center bg-white p-10 rounded-3xl shadow-xl border border-slate-100">
        <div>
            <!-- Ikon Peringatan -->
            <div class="mx-auto h-24 w-24 bg-red-50 rounded-full flex items-center justify-center mb-6 ring-8 ring-red-50/50">
                <svg class="h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <h2 class="mt-6 text-3xl font-extrabold text-slate-900 tracking-tight">Akses Ditolak</h2>
            <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                Maaf, Anda tidak memiliki izin (role) untuk mengakses halaman ini. <br>
                Silakan kembali ke menu yang tersedia untuk Anda.
            </p>
        </div>
        
        <div class="mt-8">
            <!-- Tombol Kembali -->
            <a href="{{ url()->previous() }}" class="w-full inline-flex justify-center items-center px-6 py-3.5 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Halaman Sebelumnya
            </a>
            
            <!-- Tombol ke Beranda (Opsional) -->
            <a href="{{ route('booking.index') }}" class="mt-4 w-full inline-flex justify-center items-center px-6 py-3 border border-slate-300 text-sm font-bold rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition-all">
                Ke Halaman Utama
            </a>
        </div>
    </div>
</div>
@endsection