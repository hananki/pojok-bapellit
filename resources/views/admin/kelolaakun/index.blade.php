@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $isAdmin = $user?->isAdmin() ?? false;
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.user';
@endphp

@extends($layout)

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-slate-50 min-h-screen">
    
    <!-- ===== HEADER CARD (DENGAN ANIMASI ROTATE) ===== -->
    <div class="mb-8 bg-gradient-to-r from-green-500 via-teal-600 to-blue-600 rounded-2xl shadow-lg p-6 sm:p-8 overflow-hidden relative">
        
        <!-- Ornamen Background (Animasi Berputar) -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 pointer-events-none rotate-anim"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 rounded-full bg-black/10 pointer-events-none rotate-anim-reverse"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0 shadow-inner border border-white/30">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight drop-shadow-sm">Kelola Akun Pengguna</h1>
                <p class="text-sm sm:text-base text-teal-50 mt-1.5 leading-relaxed max-w-3xl">Manajemen data akses, profil, dan hak pengguna sistem.</p>
            </div>
        </div>
    </div>

    <!-- ===== ACTION BAR (FILTER) ===== -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
        <form id="filterForm" action="{{ route('admin.kelolaakun.index') }}" method="GET" class="flex flex-col gap-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-12">
                <div class="sm:col-span-2 xl:col-span-4">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors group-focus-within:text-teal-600">
                            <svg class="w-5 h-5 text-slate-400 group-focus-within:text-teal-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau NIP..." 
                               class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 focus:bg-white transition-all">
                    </div>
                </div>
                <div class="sm:col-span-1 xl:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bidang</label>
                    <select name="bidang" onchange="this.form.submit()" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 focus:bg-white cursor-pointer transition-all">
                        <option value="">Semua Bidang</option>
                        <option value="__tanpa_bidang__" {{ request('bidang') === '__tanpa_bidang__' ? 'selected' : '' }}>Tanpa Bidang</option>
                        @if(isset($bidangList) && count($bidangList) > 0)
                            @foreach($bidangList as $b)
                                @php $cleanBidang = strtoupper(trim($b)); $displayBidang = ucwords(strtolower(str_replace('_', ' ', $cleanBidang))); @endphp
                                <option value="{{ $cleanBidang }}" {{ request('bidang') == $cleanBidang ? 'selected' : '' }}>{{ $displayBidang }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="sm:col-span-1 xl:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jabatan</label>
                    <select name="jabatan" onchange="this.form.submit()" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 focus:bg-white cursor-pointer transition-all">
                        <option value="">Semua Jabatan</option>
                        <option value="__tanpa_jabatan__" {{ request('jabatan') === '__tanpa_jabatan__' ? 'selected' : '' }}>Tanpa Jabatan</option>
                        @foreach($jabatanList as $j)
                            <option value="{{ $j }}" {{ request('jabatan') == $j ? 'selected' : '' }}>{{ Str::limit($j, 40) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-1 xl:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Peran</label>
                    <select name="role" onchange="this.form.submit()" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 focus:bg-white cursor-pointer transition-all">
                        <option value="">Semua Peran</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                        <option value="pengelola_surat" {{ request('role') === 'pengelola_surat' ? 'selected' : '' }}>Pengelola Surat</option>
                        <option value="pengelola_aula" {{ request('role') === 'pengelola_aula' ? 'selected' : '' }}>Pengelola Aula</option>
                        <option value="pengelola_absensi" {{ request('role') === 'pengelola_absensi' ? 'selected' : '' }}>Pengelola Absensi</option>
                    </select>
                </div>
                <div class="sm:col-span-1 xl:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 focus:bg-white cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center">
                <button type="submit" class="flex-1 sm:flex-none px-6 py-3 bg-teal-600 text-white text-sm font-bold rounded-xl hover:bg-teal-700 shadow-md shadow-teal-500/20 flex items-center justify-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> Cari
                </button>
                @if(request()->filled('bidang') || request()->filled('jabatan') || request()->filled('role') || request()->filled('status') || request()->filled('search'))
                    <a href="{{ route('admin.kelolaakun.index') }}" class="px-5 py-3 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 flex items-center justify-center gap-2 transition-all" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Reset
                    </a>
                @endif
                <a href="{{ route('admin.kelolaakun.create') }}" class="w-full sm:ml-auto sm:w-auto px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-teal-600 shadow-md shadow-teal-500/20 flex items-center justify-center gap-2 whitespace-nowrap transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Tambah Akun
                </a>
            </div>
        </form>
    </div>

    @php
        $groupedRaw = $users->getCollection()->groupBy(function ($u) {
            $b = trim(strtoupper($u->bidang ?? ''));
            $b = preg_replace('/\s+/', ' ', $b);
            return $b === '' ? 'TANPA BIDANG' : $b;
        });
        $semuaBidang = $groupedRaw->keys()->toArray();
        $kepalaBadan = null; $sekretariat = null; $tanpaBidang = null; $bidangLainnya = [];
        foreach ($semuaBidang as $bidang) {
            if ($bidang === 'KEPALA BADAN') $kepalaBadan = $bidang;
            elseif ($bidang === 'SEKRETARIAT') $sekretariat = $bidang;
            elseif ($bidang === 'TANPA BIDANG') $tanpaBidang = $bidang;
            else $bidangLainnya[] = $bidang;
        }
        $urutanBidang = [];
        if ($kepalaBadan) $urutanBidang[] = $kepalaBadan;
        if ($sekretariat) $urutanBidang[] = $sekretariat;
        foreach ($bidangLainnya as $b) $urutanBidang[] = $b;
        if ($tanpaBidang) $urutanBidang[] = $tanpaBidang;
        $grouped = collect($urutanBidang)->mapWithKeys(fn($bidang) => [$bidang => $groupedRaw->get($bidang, collect())])->filter(fn($items) => $items->isNotEmpty());
        $globalNo = $users->firstItem() ?? 1;
    @endphp

    <!-- ===== MOBILE CARDS (HP) ===== -->
    <div class="lg:hidden space-y-6">
        @forelse($grouped as $bidang => $groupUsers)
            <div>
                <div class="flex items-center gap-2 mb-3 px-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500 shrink-0 shadow-sm"></span>
                    <span class="text-xs font-bold text-teal-800 tracking-wide flex-1 leading-tight">
                        @if($bidang === 'TANPA BIDANG') BELUM MEMILIKI BIDANG @else {{ ucwords(strtolower(str_replace('_', ' ', $bidang))) }} @endif
                    </span>
                    <span class="text-[11px] font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full border border-teal-100 whitespace-nowrap">{{ $groupUsers->count() }} Orang</span>
                </div>
                <div class="space-y-3">
                    @foreach($groupUsers as $u)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 hover:shadow-md transition-shadow">
                        <div class="flex gap-3">
                            @if($u->profile_photo)
                                <button type="button" onclick="previewPhoto('{{ asset('storage/' . $u->profile_photo) }}', '{{ addslashes($u->name) }}')" class="shrink-0">
                                    <img src="{{ asset('storage/' . $u->profile_photo) }}" alt="Foto {{ $u->name }}" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-slate-200">
                                </button>
                            @else
                                <button type="button" onclick="previewInitial('{{ addslashes($u->name) }}')" class="w-12 h-12 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold text-sm shadow-sm shrink-0">{{ strtoupper(substr($u->name, 0, 1)) }}</button>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-slate-900 leading-tight truncate">{{ $u->name }}</div>
                                <div class="text-xs text-slate-500 truncate flex items-center gap-1 mt-0.5"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ $u->email ?? ($u->username ? '@'.$u->username : '-') }}</div>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @if($u->role === 'admin')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Admin</span>
                                    @elseif($u->role === 'pengelola_surat')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pengelola Surat</span>
                                    @elseif($u->role === 'pengelola_aula')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Pengelola Aula</span>
                                    @elseif($u->role === 'pengelola_absensi')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200">Pengelola Absensi</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">User</span>
                                    @endif
                                    @if($u->status === 'aktif')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Nonaktif</span>
                                    @endif
                                    @if($u->golongan)<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">{{ $u->golongan }}</span>@endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-slate-50 rounded-xl px-3 py-2 border border-slate-100">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">NIP / PPPK</div>
                                <div class="font-mono font-medium text-slate-700 truncate">{{ $u->nip ?: '-' }}</div>
                            </div>
                            <div class="bg-slate-50 rounded-xl px-3 py-2 border border-slate-100">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Jabatan</div>
                                <div class="font-medium text-slate-700 leading-tight line-clamp-2">{{ $u->jabatan ?: '-' }}</div>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.kelolaakun.edit', $u->id) }}" class="flex-1 py-2.5 bg-teal-50 text-teal-700 border border-teal-200 text-sm font-bold rounded-xl text-center hover:bg-teal-100 flex items-center justify-center gap-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Edit
                            </a>
                            <form id="delete-form-m-{{ $u->id }}" action="{{ route('admin.kelolaakun.destroy', $u->id) }}" method="POST" class="flex-1">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->name) }}')" class="w-full py-2.5 bg-red-50 border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-100 flex items-center justify-center gap-1.5 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-10 text-center">
                <div class="bg-slate-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3"><svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                <p class="text-sm font-medium text-slate-500">Belum ada data pengguna.</p>
            </div>
        @endforelse
    </div>

    <!-- ===== DESKTOP TABLE ===== -->
    <div class="hidden lg:block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="text-xs font-bold text-teal-800 uppercase tracking-wider border-b border-teal-100 bg-teal-50">
                        <th class="px-4 py-4 w-14 text-center">No</th>
                        <th class="px-4 py-4 w-16 text-center">Foto</th>
                        <th class="px-4 py-4">Nama Pengguna</th>
                        <th class="px-4 py-4">NIP / PPPK</th>
                        <th class="px-4 py-4">Jabatan</th>
                        <th class="px-4 py-4 text-center">Gol</th>
                        <th class="px-4 py-4 text-center">Peran</th>
                        <th class="px-4 py-4 text-center">Status</th>
                        <th class="px-4 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($grouped as $bidang => $groupUsers)
                        <tr class="bg-gradient-to-r from-teal-50/80 to-emerald-50/80 border-y border-teal-100">
                            <td colspan="9" class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-teal-500"></div>
                                    <span class="text-sm font-bold text-teal-900 tracking-wide">@if($bidang === 'TANPA BIDANG') BELUM MEMILIKI BIDANG @else {{ ucwords(strtolower(str_replace('_', ' ', $bidang))) }} @endif</span>
                                    <span class="text-xs font-bold text-teal-700 bg-white/70 px-2.5 py-0.5 rounded-full border border-teal-200">{{ $groupUsers->count() }} Orang</span>
                                </div>
                            </td>
                        </tr>
                        @foreach($groupUsers as $u)
                        <tr class="hover:bg-teal-50/40 transition-colors group">
                            <td class="px-4 py-4 text-sm font-semibold text-slate-500 text-center">{{ $globalNo++ }}</td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex justify-center">
                                    @if($u->profile_photo)
                                        <button type="button" onclick="previewPhoto('{{ asset('storage/' . $u->profile_photo) }}', '{{ addslashes($u->name) }}')" class="rounded-full overflow-hidden ring-2 ring-transparent hover:ring-teal-400 transition-all cursor-zoom-in"><img src="{{ asset('storage/' . $u->profile_photo) }}" alt="Foto {{ $u->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-slate-200"></button>
                                    @else
                                        <button type="button" onclick="previewInitial('{{ addslashes($u->name) }}')" class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold text-xs shadow-sm ring-1 ring-slate-200">{{ strtoupper(substr($u->name, 0, 1)) }}</button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-bold text-slate-900">{{ $u->name }}</div>
                                <div class="text-xs text-slate-500 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ Str::limit($u->email ?? ($u->username ? '@'.$u->username : '-'), 28) }}</div>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-600 font-mono">{{ $u->nip ?: '-' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700 max-w-[180px] truncate" title="{{ $u->jabatan }}">{{ $u->jabatan ?: '-' }}</td>
                            <td class="px-4 py-4 text-center">@if($u->golongan)<span class="inline-flex px-2 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">{{ $u->golongan }}</span>@else<span class="text-slate-300">-</span>@endif</td>
                            <td class="px-4 py-4 text-center">
                                @if($u->role === 'admin')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Admin</span>
                                @elseif($u->role === 'pengelola_surat')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Pengelola Surat</span>
                                @elseif($u->role === 'pengelola_aula')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Pengelola Aula</span>
                                @elseif($u->role === 'pengelola_absensi')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700 border border-pink-200">Pengelola Absensi</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">User </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">@if($u->status === 'aktif')<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif</span>@else<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Nonaktif</span>@endif</td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.kelolaakun.edit', $u->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-teal-600 bg-teal-50 hover:bg-teal-600 hover:text-white rounded-lg border border-teal-100 hover:border-teal-600 shadow-sm transition-all" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></a>
                                    <form id="delete-form-{{ $u->id }}" action="{{ route('admin.kelolaakun.destroy', $u->id) }}" method="POST" class="inline-flex">@csrf @method('DELETE')<button type="button" onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->name) }}')" class="inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg border border-red-100 hover:border-red-600 shadow-sm transition-all" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr><td colspan="9" class="px-6 py-16 text-center text-slate-500">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-slate-500">Menampilkan <span class="font-bold text-slate-800">{{ $users->firstItem() }}</span> - <span class="font-bold text-slate-800">{{ $users->lastItem() }}</span> dari <span class="font-bold text-slate-800">{{ $users->total() }}</span> akun</div>
            <div class="flex items-center gap-1">
                @if ($users->onFirstPage())<span class="px-3 py-2 text-sm text-slate-400 bg-white border border-slate-200 rounded-lg cursor-not-allowed">&lsaquo;</span>@else<a href="{{ $users->previousPageUrl() }}" class="px-3 py-2 text-sm text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors">&lsaquo;</a>@endif
                @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                    @if ($page == $users->currentPage())<span class="px-3 py-2 text-sm text-white bg-teal-600 border border-teal-600 rounded-lg font-bold shadow-sm">{{ $page }}</span>@else<a href="{{ $url }}" class="px-3 py-2 text-sm text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors">{{ $page }}</a>@endif
                @endforeach
                @if ($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}" class="px-3 py-2 text-sm text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors">&rsaquo;</a>@else<span class="px-3 py-2 text-sm text-slate-400 bg-white border border-slate-200 rounded-lg cursor-not-allowed">&rsaquo;</span>@endif
            </div>
        </div>
        @endif
    </div>

    @if($users->hasPages())
    <div class="lg:hidden mt-4 flex flex-col items-center gap-3">
        <div class="text-xs text-slate-500 font-medium">{{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} akun</div>
        <div class="flex items-center gap-1">
            @if ($users->onFirstPage())<span class="px-4 py-2.5 text-sm text-slate-400 bg-white border border-slate-200 rounded-xl cursor-not-allowed">â€¹</span>@else<a href="{{ $users->previousPageUrl() }}" class="px-4 py-2.5 text-sm text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">â€¹</a>@endif
            @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                @if ($page == $users->currentPage())<span class="px-4 py-2.5 text-sm text-white bg-teal-600 rounded-xl font-bold shadow-sm">{{ $page }}</span>@else<a href="{{ $url }}" class="px-4 py-2.5 text-sm text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">{{ $page }}</a>@endif
            @endforeach
            @if ($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}" class="px-4 py-2.5 text-sm text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">â€º</a>@else<span class="px-4 py-2.5 text-sm text-slate-400 bg-white border border-slate-200 rounded-xl cursor-not-allowed">â€º</span>@endif
        </div>
    </div>
    @endif
</div>

<!-- Modal Preview Foto -->
<div id="photoModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4 transition-opacity duration-300" onclick="if(event.target === this) closePhoto()">
    <button type="button" onclick="closePhoto()" class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center backdrop-blur-md border border-white/20 z-10 transition-colors" aria-label="Tutup"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    <div class="max-w-3xl max-h-[85vh] flex flex-col items-center" onclick="event.stopPropagation()">
        <img id="previewImg" src="" alt="Preview" class="max-w-full max-h-[70vh] rounded-2xl shadow-2xl object-contain bg-white">
        <p id="previewName" class="mt-4 text-white text-lg font-bold drop-shadow-lg text-center"></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function previewPhoto(url, name) {
        const modal = document.getElementById('photoModal'); const img = document.getElementById('previewImg'); const nameEl = document.getElementById('previewName');
        img.src = url; img.style.display = 'block'; nameEl.textContent = name;
        modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow = 'hidden';
    }
    function previewInitial(name) {
        const modal = document.getElementById('photoModal'); const img = document.getElementById('previewImg'); const nameEl = document.getElementById('previewName');
        img.style.display = 'none';
        nameEl.innerHTML = `<div class="flex flex-col items-center"><div class="w-32 h-32 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold text-5xl shadow-2xl">${name.charAt(0).toUpperCase()}</div><p class="mt-4 text-white text-lg font-bold drop-shadow-lg">${name}</p><p class="mt-1 text-white/60 text-sm">Belum mengunggah foto profil</p></div>`;
        modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow = 'hidden';
    }
    function closePhoto() { const modal = document.getElementById('photoModal'); modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow = ''; }
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closePhoto(); });
    
    function confirmDelete(id, name) {
        Swal.fire({ 
            title: 'Hapus Akun?', 
            html: `Anda akan menghapus akun <strong>${name}</strong> secara permanen.<br><span class="text-sm text-slate-500 mt-2 block">Tindakan ini tidak dapat dibatalkan.</span>`, 
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#64748b', 
            confirmButtonText: 'Ya, Hapus Permanen', 
            cancelButtonText: 'Batal', 
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
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } }); 
                const f = document.getElementById('delete-form-' + id) || document.getElementById('delete-form-m-' + id); 
                if(f) f.submit(); 
            }
        });
    }
</script>

<style>
    /* Animasi Berputar untuk Header */
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