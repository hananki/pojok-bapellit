@php
    $isAdmin = Auth::user()?->isAdmin() ?? false;
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.user';
@endphp
@extends($layout)
@section('title', 'Detail Kendaraan')
@section('content')

<style>
    .detail-gradient-bg {
        background: linear-gradient(135deg, #18181b 0%, #09090b 100%);
        position: relative;
        overflow: hidden;
    }
    
    .info-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        transition: all 0.3s;
    }

    html.dark .info-card {
        background: #141414 !important;
        border-color: #262626 !important;
    }
    
    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    html.dark .info-card:hover {
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.7);
        border-color: #404040 !important;
    }
    
    .tax-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        transition: all 0.3s;
    }

    html.dark .tax-card {
        background: linear-gradient(135deg, #141414 0%, #171717 100%) !important;
        border-color: #262626 !important;
    }
    
    .tax-card:hover {
        transform: scale(1.02);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }

    html.dark .tax-card:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.8);
        border-color: #404040 !important;
    }
    
    .action-btn {
        transition: all 0.3s;
    }
    
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .image-container {
        position: relative;
        overflow: hidden;
        background: #000000;
    }
    
    .image-container img {
        transition: transform 0.5s;
    }
    
    .image-container:hover img {
        transform: scale(1.05);
    }
</style>

<div class="min-h-screen pb-12">
    <!-- Back Button -->
    <div class="max-w-6xl mx-auto px-4 py-6">
        <a href="{{ route($isAdmin ? 'admin.kendaraan.index' : 'kendaraan.index') }}" class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold transition group">
            <svg class="w-6 h-6 transform group-hover:-translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Main Card -->
        <div class="bg-white dark:bg-[#0d0d0d] rounded-3xl shadow-2xl dark:border dark:border-[#1f1f1f] overflow-hidden">
            <!-- Hero Image -->
            <div class="image-container h-80 flex items-center justify-center relative bg-slate-900 dark:bg-black">
                @if($kendaraan->foto_url)
                    <img src="{{ $kendaraan->foto_url }}" class="w-full h-full object-cover" alt="{{ $kendaraan->plat_nomor }}">
                @else
                    <div class="text-9xl">🚗</div>
                @endif
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Header Info -->
                <div class="flex flex-col md:flex-row justify-between items-start gap-6 mb-8">
                    <div>
                        <h1 class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{ $kendaraan->merk ?: 'Merk belum diisi' }}</h1>
                        <p class="text-xl text-gray-600 dark:text-neutral-400">Plat Nomor: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $kendaraan->plat_nomor }}</span></p>
                    </div>
                    
                    @if($isAdmin)
                    <div class="flex gap-3 flex-shrink-0">
                        <a href="{{ route('admin.kendaraan.edit', $kendaraan) }}"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl font-bold text-white shadow-lg hover:opacity-90 active:scale-95 transition-all"
                           style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                        <form action="{{ route('admin.kendaraan.destroy', $kendaraan) }}" method="POST" id="delete-form-show">
                            @csrf @method('DELETE')
                            <button type="button"
                                    onclick="confirmDeleteShow('{{ $kendaraan->plat_nomor }}')"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl font-bold text-white shadow-lg hover:opacity-90 active:scale-95 transition-all"
                                    style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                    @endif
                </div>

                <!-- Info Grid -->
                <div class="grid md:grid-cols-3 gap-4 mb-8">
                    @if($canSeeNomorRangka ?? false)
                    <div class="info-card rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-purple-100 dark:bg-purple-950/50 p-2 rounded-xl">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-600 dark:text-neutral-400">Nomor Rangka</p>
                        </div>
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ $kendaraan->nomor_rangka ?: '-' }}</p>
                    </div>
                    @endif
                    
                    <div class="info-card rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-blue-100 dark:bg-blue-950/50 p-2 rounded-xl">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-600 dark:text-neutral-400">Unit Kerja</p>
                        </div>
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ $kendaraan->unit_kerja ?: '-' }}</p>
                    </div>
                    
                    <div class="info-card rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-green-100 dark:bg-green-950/50 p-2 rounded-xl">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-600 dark:text-neutral-400">Pemakai</p>
                        </div>
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ $kendaraan->user?->name ?: ($kendaraan->pemakai ?: '-') }}</p>
                        @if($kendaraan->user)
                            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">{{ $kendaraan->user->email }}</p>
                        @endif
                    </div>
                </div>

                <!-- Tax Cards -->
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    @foreach([['Pajak Tahunan', $kendaraan->pajakTahunanAktif], ['Pajak 5 Tahunan', $kendaraan->pajakLimaTahunanAktif]] as [$label, $pajak])
                    <div class="tax-card rounded-3xl p-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 text-9xl opacity-5"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-3 rounded-2xl text-white">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-black text-gray-900 dark:text-white">{{ $label }}</h2>
                            </div>
                            
                            @if($pajak)
                                <div class="bg-white dark:bg-[#1a1a1a] dark:border dark:border-[#262626] rounded-2xl p-4 mb-4">
                                    @include('kendaraan.partials.badge-pajak', ['pajak' => $pajak])
                                </div>
                                <p class="text-sm text-gray-700 dark:text-neutral-300 mb-4">
                                    <span class="font-bold">Jatuh tempo:</span> 
                                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $pajak->tanggal_jatuh_tempo->format('d M Y') }}</span>
                                </p>
                                
                                @if($isAdmin)
                                <form action="{{ route('admin.kendaraan.tandai-bayar', $pajak) }}" method="POST" onsubmit="confirmMarkAsPaid(event, '{{ $label }}', '{{ $kendaraan->plat_nomor }}')">
                                    @csrf
                                    <button class="w-full action-btn bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Tandai Sudah Bayar
                                    </button>
                                </form>
                                @endif
                            @else
                                <div class="bg-white dark:bg-[#1a1a1a] dark:border dark:border-[#262626] rounded-2xl p-6 text-center">
                                    <div class="text-5xl mb-3"></div>
                                    <p class="text-gray-600 dark:text-neutral-400 font-semibold">Belum ada pajak aktif</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Notes -->
                @if($kendaraan->catatan)
                <div class="bg-amber-50/60 dark:bg-amber-950/20 border-2 border-amber-200 dark:border-amber-900/50 rounded-3xl p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="bg-amber-100 dark:bg-amber-950/60 p-2 rounded-xl">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Catatan</h3>
                    </div>
                    <p class="text-gray-700 dark:text-neutral-300 leading-relaxed">{{ $kendaraan->catatan }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDeleteShow(platNomor) {
        Swal.fire({
            title: 'Hapus Kendaraan?',
            html: `Yakin ingin menghapus kendaraan <strong>${platNomor}</strong>?<br><small class="text-gray-500">Semua data pajak terkait juga akan terhapus dan tidak bisa dikembalikan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-show').submit();
            }
        });
    }

    function confirmMarkAsPaid(event, taxLabel, plateNumber) {
        event.preventDefault();
        const form = event.currentTarget;
        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: `Yakin pajak <strong>${taxLabel}</strong> kendaraan <strong>${plateNumber}</strong> sudah dibayar?<br><small class="text-gray-500">Periode berikutnya akan dibuat otomatis.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '✓ Ya, Sudah Dibayar',
            cancelButtonText: '✕ Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-2xl font-bold px-8',
                cancelButton: 'rounded-2xl font-bold px-8'
            }
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    }
</script>
@endpush
