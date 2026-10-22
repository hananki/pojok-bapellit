@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $isAdmin = $user?->isAdmin() ?? false;
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.user';
@endphp

@extends($layout)

@section('title', 'Riwayat Booking Aula')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-slate-50 min-h-screen" x-data="riwayatManager()" x-init="initPage()">
    
    <!-- Header Card (Warna Navbar) -->
    <div class="mb-8 bg-gradient-to-r from-green-500 via-teal-600 to-blue-600 rounded-2xl shadow-lg p-6 sm:p-8 overflow-hidden relative" 
         x-show="showPage" 
         x-transition:enter="transition ease-out duration-700" 
         x-transition:enter-start="opacity-0 -translate-y-4" 
         x-transition:enter-end="opacity-100 translate-y-0">
        
        <!-- Ornamen Background (Efek Cahaya) -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 rounded-full bg-black/10 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0 shadow-inner border border-white/30">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight drop-shadow-sm">
                    {{ $isAdmin ? 'Riwayat Booking' : 'Riwayat Booking Saya' }}
                </h1>
                <p class="text-sm sm:text-base text-teal-50 mt-1.5 leading-relaxed max-w-3xl">
                    {{ $isAdmin 
                        ? 'Kelola dan pantau seluruh riwayat pengajuan booking aula dalam satu panel kendali terpadu.' 
                        : 'Lihat dan pantau seluruh riwayat pengajuan booking aula yang pernah Anda lakukan.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex flex-col md:flex-row gap-4 mb-8" x-show="showPage" 
         x-transition:enter="transition ease-out duration-700 delay-100" 
         x-transition:enter-start="opacity-0 translate-y-4" 
         x-transition:enter-end="opacity-100 translate-y-0">
        <!-- Search -->
        <div class="relative flex-1 group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-teal-600">
                <svg class="w-5 h-5 text-slate-400 group-focus-within:text-teal-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" 
                x-model="searchQuery"
                placeholder="Cari riwayat berdasarkan nama aula, pemohon, atau keperluan..." 
                class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:outline-none text-sm bg-white shadow-sm hover:shadow-md transition-all duration-300 placeholder-slate-400">
        </div>
    </div>

    <!-- ============================================ -->
    <!-- DESKTOP TABLE VIEW (Hidden on Mobile)        -->
    <!-- ============================================ -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hidden md:block"
         x-show="showPage" 
         x-transition:enter="transition ease-out duration-700 delay-200" 
         x-transition:enter-start="opacity-0 translate-y-8" 
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-teal-50 border-b border-teal-100">
                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-800 uppercase tracking-wider">Aula</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-800 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-800 uppercase tracking-wider">Jadwal</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-800 uppercase tracking-wider">Keperluan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-teal-800 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $index => $booking)
                        @php
                            $sesiLabels = [
                                'pagi' => 'Pagi (08:00-12:00)',
                                'siang' => 'Siang (13:00-17:00)',
                                'seharian' => 'Seharian (08:00-17:00)'
                            ];
                            $statusConfig = [
                                'pending' => ['label' => 'Menunggu', 'dot' => 'bg-amber-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700 ring-1 ring-inset ring-amber-600/20'],
                                'approved' => ['label' => 'Disetujui', 'dot' => 'bg-teal-500', 'bg' => 'bg-teal-50', 'text' => 'text-teal-700 ring-1 ring-inset ring-teal-600/20'],
                                'rejected' => ['label' => 'Ditolak', 'dot' => 'bg-red-500', 'bg' => 'bg-red-50', 'text' => 'text-red-700 ring-1 ring-inset ring-red-600/20'],
                                'canceled' => ['label' => 'Dibatalkan', 'dot' => 'bg-slate-500', 'bg' => 'bg-slate-50', 'text' => 'text-slate-700 ring-1 ring-inset ring-slate-600/20'],
                                'completed' => ['label' => 'Selesai', 'dot' => 'bg-blue-500', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700 ring-1 ring-inset ring-blue-600/20'],
                            ];
                            $status = $statusConfig[$booking->status] ?? ['label' => $booking->status, 'dot' => 'bg-gray-400', 'bg' => 'bg-gray-50', 'text' => 'text-gray-700 ring-1 ring-inset ring-gray-600/20'];
                            
                            $aulaFoto = null;
                            if ($booking->aula && !empty($booking->aula->foto_urls)) {
                                $aulaFoto = $booking->aula->foto_urls[0];
                            }
                            $aulaImageFallback = 'https://ui-avatars.com/api/?name='.urlencode($booking->aula->nama ?? 'Aula').'&background=f0fdfa&color=0d9488&size=128&font-size=0.33';
                            $aulaImage = $aulaFoto ?: $aulaImageFallback;

                            $userAvatarFallback = 'https://ui-avatars.com/api/?name='.urlencode($booking->user->name ?? 'User').'&background=0d9488&color=fff&size=128';
                            $userAvatar = ($booking->user && $booking->user->profile_photo) 
                                ? asset('storage/' . $booking->user->profile_photo) 
                                : $userAvatarFallback;
                            
                            $searchString = strtolower(($booking->aula->nama ?? '') . ' ' . ($booking->user->name ?? '') . ' ' . ($booking->keperluan ?? ''));
                        @endphp
                        <tr x-show="matchesSearch('{{ addslashes($searchString) }}')"
                            x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="hover:bg-teal-50/40 transition-colors group">
                            
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <img src="{{ $aulaImage }}" alt="Foto Aula" class="w-10 h-10 rounded-xl object-cover flex-shrink-0 ring-1 ring-inset ring-teal-500/20 shadow-sm" loading="lazy">
                                    <div>
                                        <p class="font-semibold text-slate-900 text-sm md:text-base">{{ $booking->aula->nama ?? 'Aula' }}</p>
                                        <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ $booking->aula->lokasi ?? 'Lokasi belum diisi' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <img src="{{ $userAvatar }}" alt="Avatar User" class="w-10 h-10 rounded-full object-cover shadow-sm flex-shrink-0 border border-slate-200" loading="lazy">
                                    <div>
                                        <p class="font-semibold text-slate-900 text-sm md:text-base">{{ $booking->user->name ?? 'User' }}</p>
                                        <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ $booking->nama_penanggung_jawab ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900 text-sm md:text-base">{{ \Carbon\Carbon::parse($booking->tanggal_booking)->format('d M Y') }}</p>
                                <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ $sesiLabels[$booking->sesi_waktu] ?? $booking->sesi_waktu }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-700 text-sm line-clamp-2 max-w-xs">{{ $booking->keperluan }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $status['bg'] }} {{ $status['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200">
                                    <button type="button" 
                                        @click="openDetail({
                                            id: {{ $booking->id }},
                                            user: '{{ addslashes($booking->user->name ?? 'User') }}',
                                            email: '{{ addslashes($booking->user->email ?? '-') }}',
                                            aula: '{{ addslashes($booking->aula->nama ?? 'Aula') }}',
                                            lokasi: '{{ addslashes($booking->aula->lokasi ?? '-') }}',
                                            tanggal: '{{ \Carbon\Carbon::parse($booking->tanggal_booking)->format('d F Y') }}',
                                            sesi: '{{ $sesiLabels[$booking->sesi_waktu] ?? $booking->sesi_waktu }}',
                                            peserta: '{{ $booking->jumlah_peserta }} Orang',
                                            penanggung_jawab: '{{ addslashes($booking->nama_penanggung_jawab) }}',
                                            keperluan: '{{ addslashes($booking->keperluan) }}',
                                            catatan: '{{ addslashes($booking->catatan ?? '') }}',
                                            status: '{{ $booking->status }}',
                                            status_label: '{{ $status['label'] }}',
                                            status_dot: '{{ $status['dot'] }}',
                                            status_bg: '{{ $status['bg'] }}',
                                            status_text: '{{ $status['text'] }}'
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 hover:bg-teal-100 rounded-lg transition-colors focus:ring-2 focus:ring-teal-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Detail
                                    </button>

                                    @if($booking->status == 'pending')
                                        <!-- Tombol Batalkan dengan SweetAlert2 -->
                                        <button type="button" 
                                            onclick="confirmCancel({{ $booking->id }}, '{{ addslashes($booking->aula->nama ?? 'Aula') }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg transition-colors focus:ring-2 focus:ring-red-500/20">
                                            Batalkan
                                        </button>
                                        <form id="cancel-form-{{ $booking->id }}" action="{{ route('riwayat.cancel', $booking->id) }}" method="POST" class="hidden">
                                            @csrf
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="w-24 h-24 mx-auto mb-6 bg-teal-50 rounded-full flex items-center justify-center ring-8 ring-teal-50/50">
                                    <svg class="w-12 h-12 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-slate-900 mb-2">Belum Ada Riwayat Booking</p>
                                <p class="text-base text-slate-500 mb-8 max-w-sm mx-auto">Anda belum pernah melakukan booking aula. Silakan buat pengajuan booking pertama Anda.</p>
                                <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-teal-500 to-emerald-500 text-white rounded-xl hover:from-teal-600 hover:to-emerald-600 hover:shadow-lg transition-all text-base font-semibold shadow-teal-500/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Booking Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($bookings) && method_exists($bookings, 'links'))
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Menampilkan <strong class="text-slate-800">{{ $bookings->firstItem() ?? 0 }}-{{ $bookings->lastItem() ?? 0 }}</strong> dari <strong class="text-slate-800">{{ $bookings->total() }}</strong> pengajuan
                </p>
                <div>
                    {{ $bookings->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- ============================================ -->
    <!-- MOBILE CARD VIEW (Hidden on Desktop)         -->
    <!-- ============================================ -->
    <div class="md:hidden space-y-4"
         x-show="showPage" 
         x-transition:enter="transition ease-out duration-700 delay-200" 
         x-transition:enter-start="opacity-0 translate-y-8" 
         x-transition:enter-end="opacity-100 translate-y-0">
        @forelse($bookings as $booking)
            @php
                $sesiLabels = [
                    'pagi' => 'Pagi (08:00-12:00)',
                    'siang' => 'Siang (13:00-17:00)',
                    'seharian' => 'Seharian (08:00-17:00)'
                ];
                $statusConfig = [
                    'pending' => ['label' => 'Menunggu', 'dot' => 'bg-amber-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700 ring-1 ring-inset ring-amber-600/20'],
                    'approved' => ['label' => 'Disetujui', 'dot' => 'bg-teal-500', 'bg' => 'bg-teal-50', 'text' => 'text-teal-700 ring-1 ring-inset ring-teal-600/20'],
                    'rejected' => ['label' => 'Ditolak', 'dot' => 'bg-red-500', 'bg' => 'bg-red-50', 'text' => 'text-red-700 ring-1 ring-inset ring-red-600/20'],
                    'canceled' => ['label' => 'Dibatalkan', 'dot' => 'bg-slate-500', 'bg' => 'bg-slate-50', 'text' => 'text-slate-700 ring-1 ring-inset ring-slate-600/20'],
                    'completed' => ['label' => 'Selesai', 'dot' => 'bg-blue-500', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700 ring-1 ring-inset ring-blue-600/20'],
                ];
                $status = $statusConfig[$booking->status] ?? ['label' => $booking->status, 'dot' => 'bg-gray-400', 'bg' => 'bg-gray-50', 'text' => 'text-gray-700 ring-1 ring-inset ring-gray-600/20'];
                
                $aulaFoto = null;
                if ($booking->aula && !empty($booking->aula->foto_urls)) {
                    $aulaFoto = $booking->aula->foto_urls[0];
                }
                $aulaImageFallback = 'https://ui-avatars.com/api/?name='.urlencode($booking->aula->nama ?? 'Aula').'&background=f0fdfa&color=0d9488&size=128&font-size=0.33';
                $aulaImage = $aulaFoto ?: $aulaImageFallback;

                $userAvatarFallback = 'https://ui-avatars.com/api/?name='.urlencode($booking->user->name ?? 'User').'&background=0d9488&color=fff&size=128';
                $userAvatar = ($booking->user && $booking->user->profile_photo) 
                    ? asset('storage/' . $booking->user->profile_photo) 
                    : $userAvatarFallback;
                
                $searchString = strtolower(($booking->aula->nama ?? '') . ' ' . ($booking->user->name ?? '') . ' ' . ($booking->keperluan ?? ''));
            @endphp
            
            <div x-show="matchesSearch('{{ addslashes($searchString) }}')"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow duration-300">
                
                <!-- Header Card: Aula + Status -->
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $aulaImage }}" alt="Foto Aula" class="w-11 h-11 rounded-xl object-cover flex-shrink-0 ring-1 ring-inset ring-teal-500/20 shadow-sm" loading="lazy">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 text-base truncate">{{ $booking->aula->nama ?? 'Aula' }}</p>
                            <p class="text-xs text-slate-500 truncate mt-0.5">{{ $booking->aula->lokasi ?? 'Lokasi belum diisi' }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold flex-shrink-0 {{ $status['bg'] }} {{ $status['text'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                        {{ $status['label'] }}
                    </span>
                </div>

                <!-- Info Grid -->
                <div class="space-y-2.5 mb-5 bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500">Tanggal</span>
                        <span class="text-sm font-semibold text-slate-900">{{ \Carbon\Carbon::parse($booking->tanggal_booking)->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500">Sesi Waktu</span>
                        <span class="text-sm font-medium text-slate-700">{{ $sesiLabels[$booking->sesi_waktu] ?? $booking->sesi_waktu }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500">Peserta</span>
                        <span class="text-sm font-medium text-slate-700">{{ $booking->jumlah_peserta }} Orang</span>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Keperluan</span>
                    <p class="text-sm text-slate-700 leading-relaxed line-clamp-2">{{ $booking->keperluan }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                    <button type="button" 
                        @click="openDetail({
                            id: {{ $booking->id }},
                            user: '{{ addslashes($booking->user->name ?? 'User') }}',
                            email: '{{ addslashes($booking->user->email ?? '-') }}',
                            aula: '{{ addslashes($booking->aula->nama ?? 'Aula') }}',
                            lokasi: '{{ addslashes($booking->aula->lokasi ?? '-') }}',
                            tanggal: '{{ \Carbon\Carbon::parse($booking->tanggal_booking)->format('d F Y') }}',
                            sesi: '{{ $sesiLabels[$booking->sesi_waktu] ?? $booking->sesi_waktu }}',
                            peserta: '{{ $booking->jumlah_peserta }} Orang',
                            penanggung_jawab: '{{ addslashes($booking->nama_penanggung_jawab) }}',
                            keperluan: '{{ addslashes($booking->keperluan) }}',
                            catatan: '{{ addslashes($booking->catatan ?? '') }}',
                            status: '{{ $booking->status }}',
                            status_label: '{{ $status['label'] }}',
                            status_dot: '{{ $status['dot'] }}',
                            status_bg: '{{ $status['bg'] }}',
                            status_text: '{{ $status['text'] }}'
                        })"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-teal-700 bg-teal-50 border border-teal-200 hover:bg-teal-100 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Detail
                    </button>

                    @if($booking->status == 'pending')
                        <!-- Tombol Batalkan dengan SweetAlert2 (Mobile) -->
                        <button type="button" 
                            onclick="confirmCancel({{ $booking->id }}, '{{ addslashes($booking->aula->nama ?? 'Aula') }}')"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 rounded-xl transition-colors">
                            Batalkan
                        </button>
                        <form id="cancel-form-{{ $booking->id }}" action="{{ route('riwayat.cancel', $booking->id) }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-10 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-teal-400 to-emerald-500"></div>
                <div class="w-20 h-20 mx-auto mb-5 bg-teal-50 rounded-full flex items-center justify-center ring-8 ring-teal-50/50">
                    <svg class="w-10 h-10 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-lg font-bold text-slate-900 mb-1">Belum Ada Riwayat</p>
                <p class="text-sm text-slate-500 mb-6">Anda belum pernah melakukan booking aula.</p>
                <a href="{{ route('booking.index') }}" class="inline-flex items-center justify-center gap-2 w-full px-6 py-3 bg-gradient-to-r from-teal-500 to-emerald-500 text-white rounded-xl hover:from-teal-600 hover:to-emerald-600 transition-all text-sm font-semibold shadow-md shadow-teal-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Booking Sekarang
                </a>
            </div>
        @endforelse

        <!-- Pagination Mobile -->
        @if(isset($bookings) && method_exists($bookings, 'links'))
            <div class="pt-4">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================ -->
    <!-- MODAL DETAIL BOOKING                         -->
    <!-- ============================================ -->
    <div x-show="showDetailModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeDetail()"></div>
        
        <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
            <div x-show="showDetailModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="closeDetail()"
                 class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full relative z-10 max-h-[90vh] overflow-y-auto mx-2 border border-slate-200">
                
                <!-- Header Modal (Warna Navbar) -->
                <div class="flex items-center justify-between px-6 sm:px-8 py-5 sm:py-6 border-b border-teal-700/30 sticky top-0 bg-gradient-to-r from-green-500 via-teal-600 to-blue-600 z-10 rounded-t-2xl shadow-md">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-bold text-white drop-shadow-sm">
                            Detail Booking <span class="text-teal-100" x-text="'#' + detail.id"></span>
                        </h3>
                        <span class="inline-flex items-center gap-2 mt-2 px-3 py-1.5 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/30 shadow-sm"
                              x-text="detail.status_label">
                        </span>
                    </div>
                    <button @click="closeDetail()" 
                        class="p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-white/50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="px-6 sm:px-8 py-6 bg-white">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">User</p>
                            <p class="text-base font-semibold text-slate-900 mt-1" x-text="detail.user"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email</p>
                            <p class="text-base font-semibold text-slate-900 mt-1 break-all" x-text="detail.email"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aula</p>
                            <p class="text-base font-semibold text-slate-900 mt-1" x-text="detail.aula"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Lokasi</p>
                            <p class="text-base font-semibold text-slate-900 mt-1" x-text="detail.lokasi"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal</p>
                            <p class="text-base font-semibold text-slate-900 mt-1" x-text="detail.tanggal"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sesi</p>
                            <p class="text-base font-semibold text-slate-900 mt-1" x-text="detail.sesi"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Peserta</p>
                            <p class="text-base font-semibold text-slate-900 mt-1" x-text="detail.peserta"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Penanggung Jawab</p>
                            <p class="text-base font-semibold text-slate-900 mt-1" x-text="detail.penanggung_jawab"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Keperluan</p>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mt-1.5">
                                <p class="text-sm font-medium text-slate-800 leading-relaxed" x-text="detail.keperluan"></p>
                            </div>
                        </div>
                        <div class="sm:col-span-2" x-show="detail.catatan && detail.catatan !== ''">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan Tambahan</p>
                            <p class="text-sm text-slate-700 mt-1.5 leading-relaxed bg-amber-50 p-3 rounded-lg border border-amber-100" x-text="detail.catatan"></p>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="flex items-center justify-end gap-3 px-6 sm:px-8 py-5 sm:py-6 border-t border-slate-100 bg-slate-50 rounded-b-2xl sticky bottom-0">
                    <button @click="closeDetail()" 
                        class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl transition shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Script SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function riwayatManager() {
        return {
            searchQuery: null,
            showPage: false,
            initPage() {
                setTimeout(() => {
                    this.showPage = true;
                    // Trigger enter animation on rows after page wrapper is visible
                    setTimeout(() => {
                        this.searchQuery = '';
                    }, 50);
                }, 50);
            },
            matchesSearch(text) {
                if (this.searchQuery === null) return false;
                if (this.searchQuery.trim() === '') return true;
                return text.includes(this.searchQuery.toLowerCase().trim());
            },
            showDetailModal: false,
            detail: {
                id: '',
                user: '',
                email: '',
                aula: '',
                lokasi: '',
                tanggal: '',
                sesi: '',
                peserta: '',
                penanggung_jawab: '',
                keperluan: '',
                catatan: '',
                status: '',
                status_label: '',
                status_dot: '',
                status_bg: '',
                status_text: ''
            },
            openDetail(data) {
                this.detail = data;
                this.showDetailModal = true;
                document.body.style.overflow = 'hidden';
            },
            closeDetail() {
                this.showDetailModal = false;
                document.body.style.overflow = '';
            }
        }
    }

    // Fungsi SweetAlert untuk Konfirmasi Pembatalan
    function confirmCancel(id, namaAula) {
        Swal.fire({
            title: 'Batalkan Booking?',
            html: `Apakah Anda yakin ingin membatalkan booking untuk <strong>${namaAula}</strong>?<br><span class="text-sm text-slate-500 mt-2 block">Tindakan ini tidak dapat dibatalkan.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', // Warna Merah
            cancelButtonColor: '#64748b',  // Warna Abu-abu
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tutup',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl border border-slate-200 shadow-xl',
                title: 'text-xl font-bold text-slate-900',
                htmlContainer: 'text-slate-600',
                confirmButton: 'rounded-xl font-semibold px-6 py-2.5',
                cancelButton: 'rounded-xl font-semibold px-6 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancel-form-' + id).submit();
            }
        })
    }
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection