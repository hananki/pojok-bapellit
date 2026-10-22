@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $currentRole = $user?->role ?? 'user';
    
    // Tentukan layout berdasarkan role
    $isPanelUser = in_array($currentRole, ['admin', 'pengelola_surat']);
    $layout = $isPanelUser ? 'layouts.admin' : 'layouts.user';
@endphp

@extends($layout)

@section('title', 'Kelola Surat')

@section('content')
<div class="max-w-[98%] mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Kelola Surat</h1>
            <p class="text-base text-gray-600 mt-1">Daftar surat yang sudah diambil pengguna</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('admin.kelolasurat.export') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-base font-medium shadow-sm whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 011.414.586l4.414 4.414A2 2 0 0119 9.414V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('admin.kelolasurat.pengaturan') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-base font-medium shadow-sm whitespace-nowrap">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Pengaturan Nomor
        </a>
        </div>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Surat</p>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 mt-2">{{ $statistics['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Bulan Ini</p>
            <p class="text-2xl lg:text-3xl font-bold text-blue-600 mt-2">{{ $statistics['bulan_ini'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hari Ini</p>
            <p class="text-2xl lg:text-3xl font-bold text-green-600 mt-2">{{ $statistics['hari_ini'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nomor Berikutnya</p>
            <p class="text-lg lg:text-xl font-bold text-indigo-600 mt-2 break-all">{{ $statistics['nomor_berikutnya'] ?? '-' }}</p>
        </div>
    </div>

    <!-- Filter & Search -->
    <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari no surat, isi surat, atau nama user..."
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none text-base bg-white shadow-sm">
        </div>
        <div class="flex gap-3 shrink-0">
            <button type="submit" class="px-6 py-3 bg-gray-800 text-white rounded-xl hover:bg-gray-900 transition text-base font-medium">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.kelolasurat.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-base font-medium text-center">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Toolbar Bulk Delete -->
    @if(isset($surats) && $surats->count() > 0)
    <div id="bulk-toolbar" class="hidden mb-4 flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-xl border border-red-100 shadow-sm bg-red-50/50">
        <div class="flex items-center gap-3">
            <input type="checkbox" 
                   id="check-all-surat" 
                   class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500 cursor-pointer"
                   onchange="toggleCheckAllSurat(this)">
            <label for="check-all-surat" class="text-sm font-semibold text-gray-700 cursor-pointer select-none">
                Pilih Semua di Halaman Ini
            </label>
        </div>
        <button type="button"
                onclick="confirmBulkDelete()"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Hapus Terpilih (<span id="selected-count">0</span>)
        </button>
    </div>
    @endif

    <!-- Tabel Surat -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- ===== DESKTOP & TABLET: TABEL ===== --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full table-fixed">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-4 text-left w-12">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500" disabled>
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[14%]">No Surat</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[18%]">Pengguna</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[10%]">Jenis</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider max-w-[200px]">Isi Surat</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[12%]">Tanggal</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[10%]">File</th>
                        <th class="px-5 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-[140px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($surats as $surat)
                        <tr class="hover:bg-indigo-50/40 transition-colors">
                            <td class="px-5 py-4 align-middle">
                                <input type="checkbox" 
                                       class="surat-checkbox w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500 cursor-pointer" 
                                       value="{{ $surat->id }}"
                                       onchange="updateSelectedCount()">
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <p class="font-bold text-red-600 text-sm truncate" title="{{ $surat->no_surat }}">
                                    {{ $surat->no_surat }}
                                </p>
                                @if($surat->no_indek)
                                    <p class="text-xs text-gray-400 mt-0.5 truncate" title="Indek: {{ $surat->no_indek }}">
                                        Indek: {{ $surat->no_indek }}
                                    </p>
                                @endif
                            </td>

                            {{-- Pengguna --}}
                            <td class="px-5 py-4 align-middle">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    @if($surat->user && $surat->user->profile_photo)
                                        <img src="{{ asset('storage/' . $surat->user->profile_photo) }}"
                                             alt="{{ $surat->user->name }}"
                                             class="w-9 h-9 rounded-full object-cover flex-shrink-0 shadow-sm ring-2 ring-white border border-gray-100">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <span class="text-white font-semibold text-xs">
                                                {{ strtoupper(substr($surat->user->name ?? 'U', 0, 1)) }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 text-sm truncate" title="{{ $surat->user->name ?? 'User' }}">
                                            {{ $surat->user->name ?? 'User' }}
                                        </p>
                                        <p class="text-xs text-gray-400 truncate" title="{{ $surat->user->bidang ?? '-' }}">
                                            {{ $surat->user->bidang ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 align-middle">
                                @if($surat->jenis_surat)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 truncate max-w-full">
                                        {{ $surat->jenis_surat }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 align-middle">
                                <p class="text-sm text-gray-700 line-clamp-2 leading-relaxed truncate" title="{{ $surat->isi_surat }}">
                                    {{ Str::limit($surat->isi_surat, 50) }}
                                </p>
                            </td>

                            <td class="px-5 py-4 align-middle">
                                <p class="font-medium text-gray-900 text-sm whitespace-nowrap">
                                    {{ $surat->tanggal ? $surat->tanggal->format('d M Y') : $surat->created_at->format('d M Y') }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $surat->created_at->format('H:i') }} WIB</p>
                            </td>

                            <td class="px-5 py-4 align-middle">
                                @if($surat->file_surat)
                                    @php
                                        $colorMap = [
                                            'red'     => 'text-red-700 bg-red-50 hover:bg-red-100 border-red-100',
                                            'blue'    => 'text-blue-700 bg-blue-50 hover:bg-blue-100 border-blue-100',
                                            'emerald' => 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-100',
                                            'gray'    => 'text-gray-700 bg-gray-100 hover:bg-gray-200 border-gray-200',
                                        ];
                                        $colorClass = $colorMap[$surat->file_color] ?? $colorMap['gray'];
                                    @endphp
                                    <a href="{{ route('admin.kelolasurat.download-file', $surat->id) }}"
                                        title="Download {{ $surat->file_label }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold {{ $colorClass }} border rounded-md transition">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $surat->file_label }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 align-middle">
                                {{-- DROPDOWN MENU DENGAN ALPINE.JS --}}
                                <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                                    {{-- Tombol Detail Utama --}}
                                    <button type="button"
                                        class="btn-detail inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-md transition shadow-sm"
                                        title="Detail"
                                        data-surat="{{ json_encode([
                                            'no_surat' => $surat->no_surat,
                                            'user' => $surat->user->name ?? 'User',
                                            'bidang' => $surat->user->bidang ?? '-',
                                            'email' => $surat->user->email ?? '-',
                                            'jenis_surat' => $surat->jenis_surat ?? '-',
                                            'sifat_surat' => $surat->sifat_surat ?? '-',
                                            'tanggal' => $surat->tanggal ? $surat->tanggal->format('d F Y') : $surat->created_at->format('d F Y'),
                                            'asal_surat' => $surat->asal_surat ?? '-',
                                            'alamat_tujuan' => $surat->alamat_tujuan ?? '-',
                                            'isi_surat' => $surat->isi_surat,
                                            'no_indek' => $surat->no_indek ?? '-',
                                            'banyak_lampiran' => $surat->banyak_lampiran ?? 0,
                                            'keterangan' => $surat->keterangan ?? '-',
                                            'file_url' => $surat->file_surat ? route('admin.kelolasurat.download-file', $surat->id) : '',
                                            'file_label' => $surat->file_label ?? '-',
                                        ]) }}">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Detail
                                    </button>

                                    {{-- Tombol Titik Tiga --}}
                                    <button @click="open = !open" type="button" 
                                        class="inline-flex items-center justify-center w-8 h-8 ml-1 text-gray-500 bg-white border border-gray-200 rounded-md hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>

                                    {{-- Dropdown Menu --}}
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute right-0 z-20 mt-2 w-40 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                         style="display: none;">
                                        <div class="py-1">
                                            <a href="{{ route('admin.kelolasurat.edit', $surat->id) }}"
                                               class="group flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                                <svg class="w-4 h-4 mr-2.5 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </a>

                                            @if($surat->file_surat)
                                                <a href="{{ route('admin.kelolasurat.download-file', $surat->id) }}"
                                                   class="group flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition">
                                                    <svg class="w-4 h-4 mr-2.5 text-gray-400 group-hover:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                    </svg>
                                                    Download
                                                </a>
                                            @endif

                                            <form action="{{ route('admin.kelolasurat.destroy', $surat->id) }}" method="POST" onsubmit="return confirm('Hapus surat ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="group w-full text-left flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                                    <svg class="w-4 h-4 mr-2.5 text-red-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-700 mb-1">Belum Ada Surat Diambil</p>
                                <p class="text-base text-gray-500">Belum ada pengguna yang mengambil surat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ===== MOBILE: CARD VIEW ===== --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($surats as $surat)
                <div class="p-4 hover:bg-indigo-50/30 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-red-600 text-base truncate">{{ $surat->no_surat }}</p>
                            @if($surat->no_indek)
                                <p class="text-xs text-gray-400 mt-0.5">Indek: {{ $surat->no_indek }}</p>
                            @endif
                        </div>
                        @if($surat->file_surat)
                            @php
                                $colorMap = [
                                    'red'     => 'text-red-700 bg-red-50 border-red-100',
                                    'blue'    => 'text-blue-700 bg-blue-50 border-blue-100',
                                    'emerald' => 'text-emerald-700 bg-emerald-50 border-emerald-100',
                                    'gray'    => 'text-gray-700 bg-gray-100 border-gray-200',
                                ];
                                $colorClass = $colorMap[$surat->file_color] ?? $colorMap['gray'];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold {{ $colorClass }} border rounded-md flex-shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                {{ $surat->file_label }}
                            </span>
                        @endif
                    </div>

                    {{-- Pengguna --}}
                    <div class="flex items-center gap-3 mb-3">
                        @if($surat->user && $surat->user->profile_photo)
                            <img src="{{ asset('storage/' . $surat->user->profile_photo) }}"
                                 alt="{{ $surat->user->name }}"
                                 class="w-10 h-10 rounded-full object-cover flex-shrink-0 shadow-sm ring-2 ring-white border border-gray-100">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <span class="text-white font-semibold text-sm">
                                    {{ strtoupper(substr($surat->user->name ?? 'U', 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900 text-sm truncate">{{ $surat->user->name ?? 'User' }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $surat->user->bidang ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Jenis</p>
                            @if($surat->jenis_surat)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 truncate max-w-full">
                                    {{ $surat->jenis_surat }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tanggal</p>
                            <p class="font-medium text-gray-900 text-sm">
                                {{ $surat->tanggal ? $surat->tanggal->format('d M Y') : $surat->created_at->format('d M Y') }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $surat->created_at->format('H:i') }} WIB</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Isi Surat</p>
                        <p class="text-sm text-gray-700 line-clamp-2 leading-relaxed">
                            {{ Str::limit($surat->isi_surat, 100) }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-3 border-t border-gray-100">
                        <a href="{{ route('admin.kelolasurat.edit', $surat->id) }}"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </a>

                        <button type="button"
                            class="btn-detail inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition shadow-sm"
                            data-surat="{{ json_encode([
                                'no_surat' => $surat->no_surat,
                                'user' => $surat->user->name ?? 'User',
                                'bidang' => $surat->user->bidang ?? '-',
                                'email' => $surat->user->email ?? '-',
                                'jenis_surat' => $surat->jenis_surat ?? '-',
                                'sifat_surat' => $surat->sifat_surat ?? '-',
                                'tanggal' => $surat->tanggal ? $surat->tanggal->format('d F Y') : $surat->created_at->format('d F Y'),
                                'asal_surat' => $surat->asal_surat ?? '-',
                                'alamat_tujuan' => $surat->alamat_tujuan ?? '-',
                                'isi_surat' => $surat->isi_surat,
                                'no_indek' => $surat->no_indek ?? '-',
                                'banyak_lampiran' => $surat->banyak_lampiran ?? 0,
                                'keterangan' => $surat->keterangan ?? '-',
                                'file_url' => $surat->file_surat ? route('admin.kelolasurat.download-file', $surat->id) : '',
                                'file_label' => $surat->file_label ?? '-',
                            ]) }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Detail
                        </button>

                        @if($surat->file_surat)
                            <a href="{{ route('admin.kelolasurat.download-file', $surat->id) }}"
                                class="col-span-2 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 border border-green-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download
                            </a>
                        @endif

                        <form action="{{ route('admin.kelolasurat.destroy', $surat->id) }}" method="POST" onsubmit="return confirm('Hapus surat ini?')" class="col-span-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-lg font-medium text-gray-700 mb-1">Belum Ada Surat Diambil</p>
                    <p class="text-sm text-gray-500">Belum ada pengguna yang mengambil surat.</p>
                </div>
            @endforelse
        </div>

        @if(isset($surats) && method_exists($surats, 'links'))
            <div class="px-6 py-5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gray-50">
                <p class="text-sm text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-700">{{ $surats->firstItem() ?? 0 }}</span>-<span class="font-semibold text-gray-700">{{ $surats->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-gray-700">{{ $surats->total() }}</span> surat
                </p>
                <div>{{ $surats->links() }}</div>
            </div>
        @endif
    </div>

    <!-- ============================================ -->
    <!-- MODAL DETAIL SURAT                           -->
    <!-- ============================================ -->
    <div id="detailModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeDetail()"></div>

        <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full relative z-10 max-h-[90vh] overflow-y-auto">

                <!-- Header Modal -->
                <div class="sticky top-0 bg-white flex items-center justify-between px-6 sm:px-8 py-5 border-b border-gray-200 rounded-t-2xl z-10">
                    <div class="min-w-0">
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Surat</h3>
                        <p class="text-sm text-gray-500 mt-1 truncate" id="modalNoSurat"></p>
                    </div>
                    <button onclick="closeDetail()"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="px-6 sm:px-8 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</p>
                            <p class="text-base font-semibold text-gray-900 mt-1.5" id="modalUser"></p>
                            <p class="text-xs text-gray-500 mt-0.5" id="modalBidang"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</p>
                            <p class="text-base font-semibold text-gray-900 mt-1.5" id="modalTanggal"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Surat</p>
                            <p class="text-base font-semibold text-gray-900 mt-1.5" id="modalJenis"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sifat Surat</p>
                            <p class="text-base font-semibold text-gray-900 mt-1.5" id="modalSifat"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Asal Surat / Pengirim</p>
                            <p class="text-base font-semibold text-gray-900 mt-1.5" id="modalAsal"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tujuan Surat / Penerima</p>
                            <p class="text-base font-semibold text-gray-900 mt-1.5" id="modalAlamat"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">No / Indek</p>
                            <p class="text-base font-semibold text-gray-900 mt-1.5" id="modalNoIndek"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Banyak Lampiran</p>
                            <p class="text-base font-semibold text-gray-900 mt-1.5" id="modalLampiran"></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Isi Surat</p>
                            <p class="text-base text-gray-800 mt-1.5 whitespace-pre-wrap leading-relaxed" id="modalIsi"></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</p>
                            <p class="text-base text-gray-800 mt-1.5 whitespace-pre-wrap" id="modalKeterangan"></p>
                        </div>
                        <div class="md:col-span-2" id="modalFileWrapper">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">File Surat</p>
                            <a id="modalFileLink" href="#" target="_blank"
                                class="inline-flex items-center gap-2 mt-1.5 px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Lihat / Download File (<span id="modalFileLabel"></span>)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="sticky bottom-0 flex items-center justify-end gap-3 px-6 sm:px-8 py-5 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                    <button onclick="closeDetail()"
                        class="px-6 py-2.5 text-base font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- Logic Bulk Delete ---
    let selectedSuratIds = new Set();

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.surat-checkbox:checked');
        selectedSuratIds.clear();
        
        checkboxes.forEach(cb => {
            selectedSuratIds.add(cb.value);
        });

        const count = selectedSuratIds.size;
        const toolbar = document.getElementById('bulk-toolbar');
        const countSpan = document.getElementById('selected-count');
        
        countSpan.textContent = count;
        
        if (count > 0) {
            toolbar.style.display = 'flex';
        } else {
            toolbar.style.display = 'none';
        }
    }

    function toggleCheckAllSurat(source) {
        const checkboxes = document.querySelectorAll('.surat-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = source.checked;
        });
        updateSelectedCount();
    }

    function confirmBulkDelete() {
        if (selectedSuratIds.size === 0) return;

        Swal.fire({
            title: 'Hapus Surat Terpilih?',
            html: `Anda akan menghapus <strong>${selectedSuratIds.size} surat</strong> secara permanen.<br><span class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const ids = Array.from(selectedSuratIds);
                
                Swal.fire({
                    title: 'Sedang Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route("admin.kelolasurat.bulk-destroy") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message || 'Surat terpilih telah dihapus.',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Gagal menghapus');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: error.message || 'Terjadi kesalahan saat menghapus.',
                        confirmButtonColor: '#dc2626'
                    });
                });
            }
        });
    }

    // --- Logic Modal Detail ---
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-detail').forEach(function(btn) {
            btn.addEventListener('click', function() {
                try {
                    const data = JSON.parse(this.getAttribute('data-surat'));
                    openDetail(data);
                } catch (e) {
                    console.error('Error parsing data-surat:', e);
                }
            });
        });
    });

    function openDetail(data) {
        document.getElementById('modalNoSurat').textContent = 'No: ' + (data.no_surat || '-');
        document.getElementById('modalUser').textContent = data.user || '-';
        document.getElementById('modalBidang').textContent = data.bidang || '-';
        document.getElementById('modalJenis').textContent = data.jenis_surat || '-';
        document.getElementById('modalSifat').textContent = data.sifat_surat || '-';
        document.getElementById('modalAsal').textContent = data.asal_surat || '-';
        document.getElementById('modalAlamat').textContent = data.alamat_tujuan || '-';
        document.getElementById('modalIsi').textContent = data.isi_surat || '-';
        document.getElementById('modalKeterangan').textContent = data.keterangan || '-';
        document.getElementById('modalTanggal').textContent = data.tanggal || '-';
        document.getElementById('modalNoIndek').textContent = data.no_indek || '-';
        document.getElementById('modalLampiran').textContent = (data.banyak_lampiran || 0) + ' lembar';

        const fileWrapper = document.getElementById('modalFileWrapper');
        const fileLink = document.getElementById('modalFileLink');
        const fileLabel = document.getElementById('modalFileLabel');

        if (data.file_url && data.file_url !== '') {
            fileWrapper.classList.remove('hidden');
            fileLink.href = data.file_url;
            fileLabel.textContent = data.file_label || 'File';
        } else {
            fileWrapper.classList.add('hidden');
        }

        document.getElementById('detailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDetail() {
        document.getElementById('detailModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDetail();
    });
</script>
@endsection