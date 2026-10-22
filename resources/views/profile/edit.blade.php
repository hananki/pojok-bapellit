@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $currentRole = $user?->role ?? 'user';
    
    // Tentukan layout berdasarkan role (Admin & 3 Pengelola pakai layout admin)
    $isPanelUser = in_array($currentRole, ['admin', 'pengelola_surat', 'pengelola_aula', 'pengelola_absensi']);
    $layout = $isPanelUser ? 'layouts.admin' : 'layouts.user';

    // Map status → pesan
    $statusMessages = [
        'profile-updated'       => 'Profil berhasil diperbarui.',
        'password-updated'      => 'Password berhasil diperbarui.',
        'profile-photo-updated' => 'Foto profil berhasil diperbarui.',
    ];
    $statusMessage = $statusMessages[session('status')] ?? null;

    // Class input yang sering dipakai (DRY)
    $inputClass = 'w-full h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-teal-500 focus:ring-teal-500 transition-all';
    $inputClassWithEye = $inputClass . ' pr-12';
    $inputClassReadonly = 'w-full h-11 rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm text-slate-500 cursor-not-allowed';

    // ============================================================
    // Ambil daftar Bidang & Jabatan unik dari database user
    // ============================================================
    $bidangList = \App\Models\User::query()
        ->whereNotNull('bidang')
        ->where('bidang', '!=', '')
        ->distinct()
        ->orderBy('bidang')
        ->pluck('bidang')
        ->toArray();

    $jabatanList = \App\Models\User::query()
        ->whereNotNull('jabatan')
        ->where('jabatan', '!=', '')
        ->distinct()
        ->orderBy('jabatan')
        ->pluck('jabatan')
        ->toArray();
@endphp

@extends($layout)

@section('content')

{{-- ================= HEADER (Gradient Hijau ke Biru Sesuai Navbar) ================= --}}
<div class="bg-gradient-to-r from-green-500 via-teal-600 to-blue-600 shadow-lg relative overflow-hidden">
    <!-- Ornamen Animasi -->
    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 pointer-events-none rotate-anim"></div>
    
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 relative z-10">
        <div class="flex items-center gap-5">
            <a href="{{ url()->previous() }}"
               class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center
                      text-white hover:bg-white/30 transition-all shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-teal-100">Pengaturan Akun</p>
                <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-sm">Profil Saya</h1>
                <p class="text-sm text-teal-50 mt-1">Kelola informasi pribadi dan keamanan akun Anda.</p>
            </div>
        </div>
    </div>
</div>

{{-- ================= CONTENT ================= --}}
<main class="bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 -mt-8 relative z-20">

        {{-- SUCCESS BANNER --}}
        @if(session('status') && session('status') !== 'password-updated')
            <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-white px-5 py-4 text-sm text-emerald-700 shadow-sm">
                <svg class="w-6 h-6 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ $statusMessage }}</span>
            </div>
        @endif

        {{-- ERROR BANNER --}}
        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-white px-5 py-4 shadow-sm">
                <div class="flex gap-3">
                    <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-red-700">
                        <p class="font-bold mb-1">Periksa kembali data Anda.</p>
                        <ul class="list-disc ml-4 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ============ PROFILE CARD ============ --}}
            <aside class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden h-fit">
                <div class="h-32 bg-gradient-to-r from-green-500 via-teal-600 to-blue-600 relative">
                     <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-20"></div>
                </div>

                <div class="px-6 pb-8 relative">
                    {{-- FOTO --}}
                    <form action="{{ route('akun.photo.update') }}" method="POST"
                          enctype="multipart/form-data" id="photo-form" class="-mt-16 flex justify-center">
                        @csrf
                        {{-- Wrapper Foto --}}
                        <div class="relative w-32 h-32">
                            <div class="w-32 h-32 rounded-full bg-white p-1.5 shadow-xl">
                                <div class="w-full h-full rounded-full overflow-hidden bg-slate-100 flex items-center justify-center">
                                    @if($user->profile_photo)
                                        <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                             alt="Foto Profil" class="w-full h-full object-cover">
                                    @else
                                        {{-- Tampilkan Inisial Nama jika tidak ada foto --}}
                                        <div class="w-full h-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold text-4xl shadow-inner">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Tombol Kamera --}}
                            <label for="profile_photo"
                                   class="absolute right-1 bottom-1 w-10 h-10 rounded-full bg-teal-600 text-white
                                          border-4 border-white flex items-center justify-center shadow-lg
                                          hover:bg-teal-700 cursor-pointer transition-transform hover:scale-110 z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h3l2-3h8l2 3h3v12H3V7z"/>
                                    <circle cx="12" cy="13" r="3" stroke-width="2"/>
                                </svg>
                            </label>

                            <input type="file" id="profile_photo" name="profile_photo"
                                   accept="image/jpeg,image/png,image/webp" class="hidden"
                                   onchange="document.getElementById('photo-form').submit()">
                        </div>
                    </form>

                    {{-- NAMA + ROLE --}}
                    <div class="text-center mt-6 relative z-0"> {{-- mt-6 memberi jarak agar tidak tertimpa --}}
                        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight break-words">{{ $user->name }}</h2>
                        
                        {{-- Badge Role Dinamis --}}
                        <div class="mt-3 flex justify-center">
                            @if($currentRole === 'admin')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-50 text-red-700 border border-red-200 text-xs font-bold uppercase tracking-wide">Admin</span>
                            @elseif($currentRole === 'pengelola_surat')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold uppercase tracking-wide">Pengelola Surat</span>
                            @elseif($currentRole === 'pengelola_aula')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-bold uppercase tracking-wide">Pengelola Aula</span>
                            @elseif($currentRole === 'pengelola_absensi')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-pink-50 text-pink-700 border border-pink-200 text-xs font-bold uppercase tracking-wide">Pengelola Absensi</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold uppercase tracking-wide">User Biasa</span>
                            @endif
                        </div>
                    </div>

                    <div class="my-6 border-t border-slate-100"></div>

                    {{-- INFO SINGKAT --}}
                    <div class="space-y-4">
                        @php
                            $infos = [
                                ['label' => 'Email', 'value' => $user->email ?? '-',
                                 'icon' => 'M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                                ['label' => 'No. WhatsApp', 'value' => $user->whatsapp ?? $user->phone ?? $user->no_whatsapp ?? '-',
                                 'icon' => 'M3 5a2 2 0 012-2h2.28a2 2 0 011.94 1.515L10 7a2 2 0 01-.55 1.93l-1.27 1.27a16 16 0 006.62 6.62l1.27-1.27A2 2 0 0118 15l2.485.78A2 2 0 0122 17.72V20a2 2 0 01-2 2C10.06 22 2 13.94 2 4a2 2 0 012-2h1z'],
                                ['label' => 'NIP / NI PPPK', 'value' => $user->nip ?? '-',
                                 'icon' => 'M10 6H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-5M14 4h6v6M14 4l8 8'],
                                ['label' => 'Jabatan', 'value' => $user->jabatan ?? '-',
                                 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                                ['label' => 'Golongan', 'value' => $user->golongan ?? '-',
                                 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                                ['label' => 'Bidang', 'value' => $user->bidang ?? '-',
                                 'icon' => 'M4 6h16M4 12h16M4 18h16'],
                            ];
                        @endphp

                        @foreach($infos as $info)
                            <div class="flex items-start gap-3 p-2 rounded-xl hover:bg-teal-50 transition-colors">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0 text-teal-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 pt-0.5">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $info['label'] }}</p>
                                    <p class="text-sm font-semibold text-slate-700 truncate mt-0.5">{{ $info['value'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>

            {{-- ============ RIGHT CONTENT ============ --}}
            <section class="lg:col-span-2 space-y-6">

                {{-- ===== INFORMASI PRIBADI ===== --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden"
                     x-data="formBidang('{{ old('bidang', $user->bidang ?? '') }}')">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-4 bg-slate-50/50">
                        <div class="w-12 h-12 rounded-2xl bg-teal-100 flex items-center justify-center text-teal-600 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7-7h14a7 7 0 00-7 7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Informasi Pribadi</h2>
                            <p class="text-sm text-slate-500">Perbarui data dasar akun Anda.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('akun.update') }}" class="p-6 sm:p-8">
                        @csrf @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nama (readonly) --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">
                                    Nama Lengkap
                                    <span class="text-xs text-slate-400 font-normal ml-1">(tidak dapat diubah)</span>
                                </label>
                                <input type="text" value="{{ $user->name }}" readonly
                                       class="{{ $inputClassReadonly }}">
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Email Aktif</label>
                                <input type="email" name="email"
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="example@domain.com"
                                       class="{{ $inputClass }}">
                                @error('email') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- NIP --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">NIP / NI PPPK</label>
                                <input type="text" name="nip"
                                       value="{{ old('nip', $user->nip ?? '') }}"
                                       placeholder="Contoh: 19780712 200701 2 016"
                                       class="{{ $inputClass }}">
                                @error('nip') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- No WhatsApp --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">No. WhatsApp</label>
                                <input type="text" name="whatsapp"
                                       value="{{ old('whatsapp', $user->whatsapp ?? $user->phone ?? $user->no_whatsapp ?? '') }}"
                                       placeholder="0812xxxxxxx"
                                       class="{{ $inputClass }}">
                                @error('whatsapp') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- Jabatan --}}
                            <div x-data="formJabatan('{{ old('jabatan', $user->jabatan ?? '') }}')">
                                <label class="block text-sm font-bold text-slate-700 mb-2">
                                    Jabatan <span class="text-xs text-slate-400 font-normal">(opsional)</span>
                                </label>

                                <select x-model="selected" @change="handleChange()" class="{{ $inputClass }}">
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach($jabatanList as $jab)
                                        <option value="{{ $jab }}">{{ $jab }}</option>
                                    @endforeach
                                    <option value="__lainnya__">✏️ Lainnya (isi manual)...</option>
                                </select>

                                <div x-show="isLainnya" x-cloak class="mt-3">
                                    <input type="text" x-model="customJabatan" placeholder="Ketik jabatan manual..."
                                           class="w-full h-11 rounded-xl border border-teal-300 bg-teal-50/30 px-4 text-sm text-slate-700 focus:border-teal-500 focus:ring-teal-500">
                                    <p class="text-xs text-slate-500 mt-1.5">Isi nama jabatan dengan lengkap</p>
                                </div>

                                <input type="hidden" name="jabatan" :value="finalJabatan">
                                @error('jabatan') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- Golongan --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Golongan</label>
                                <input type="text" name="golongan"
                                       value="{{ old('golongan', $user->golongan ?? '') }}"
                                       placeholder="Contoh: IV/c, III/d, IX"
                                       class="{{ $inputClass }}">
                                @error('golongan') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- Bidang --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">
                                    Asal Bidang / Bagian <span class="text-xs text-slate-400 font-normal">(opsional)</span>
                                </label>

                                <select x-model="selected" @change="handleChange()" class="{{ $inputClass }}">
                                    <option value="">-- Pilih Bidang --</option>
                                    @foreach($bidangList as $bid)
                                        <option value="{{ $bid }}">{{ $bid }}</option>
                                    @endforeach
                                    <option value="__lainnya__">✏️ Lainnya (isi manual)...</option>
                                </select>

                                <div x-show="isLainnya" x-cloak class="mt-3">
                                    <input type="text" x-model="customBidang" placeholder="Ketik nama bidang manual..."
                                           class="w-full h-11 rounded-xl border border-teal-300 bg-teal-50/30 px-4 text-sm text-slate-700 focus:border-teal-500 focus:ring-teal-500">
                                    <p class="text-xs text-slate-500 mt-1.5">Isi nama bidang dengan lengkap</p>
                                </div>

                                <input type="hidden" name="bidang" :value="finalBidang">
                                @error('bidang') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- Role (readonly) --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">
                                    Role
                                    <span class="text-xs text-slate-400 font-normal ml-1">(tidak dapat diubah)</span>
                                </label>
                                <input type="text" value="{{ $user->role ?? 'user' }}" readonly
                                       class="{{ $inputClassReadonly }} uppercase font-bold text-teal-600">
                            </div>

                            {{-- Status (readonly) --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">
                                    Status Akun
                                    <span class="text-xs text-slate-400 font-normal ml-1">(tidak dapat diubah)</span>
                                </label>
                                <input type="text" value="{{ $user->status ?? 'aktif' }}" readonly
                                       class="{{ $inputClassReadonly }} capitalize font-bold {{ $user->status === 'aktif' ? 'text-emerald-600' : 'text-red-600' }}">
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-green-500 to-teal-600
                                           text-white text-sm font-bold hover:from-green-600 hover:to-teal-700
                                           focus:ring-4 focus:ring-teal-100 transition-all shadow-md hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ===== PASSWORD ===== --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-4 bg-slate-50/50">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m0-8V7m0 0a4 4 0 018 0v4a4 4 0 01-4 4h-8a4 4 0 01-4-4V7a4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Keamanan Akun</h2>
                            <p class="text-sm text-slate-500">Gunakan password yang kuat dan mudah Anda ingat.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="p-6 sm:p-8" x-data="{ show: {} }">
                        @csrf @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @php
                                $pwdFields = [
                                    ['id' => 'current_password', 'name' => 'current_password', 'label' => 'Password Saat Ini', 'wrap' => 'md:col-span-2'],
                                    ['id' => 'password', 'name' => 'password', 'label' => 'Password Baru', 'wrap' => ''],
                                    ['id' => 'password_confirmation', 'name' => 'password_confirmation', 'label' => 'Konfirmasi Password', 'wrap' => ''],
                                ];
                            @endphp

                            @foreach($pwdFields as $f)
                                <div class="{{ $f['wrap'] }}">
                                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ $f['label'] }}</label>
                                    <div class="relative">
                                        <input :type="show['{{ $f['id'] }}'] ? 'text' : 'password'"
                                               id="{{ $f['id'] }}" name="{{ $f['name'] }}" required
                                               class="{{ $inputClassWithEye }}">

                                        <button type="button" @click="show['{{ $f['id'] }}'] = !show['{{ $f['id'] }}']"
                                                class="absolute inset-y-0 right-0 px-4 text-slate-400 hover:text-teal-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </button>
                                    </div>

                                    @if($f['id'] === 'current_password')
                                        @error('current_password')
                                            <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-900
                                           text-white text-sm font-bold hover:bg-slate-800
                                           focus:ring-4 focus:ring-slate-200 transition-all shadow-md hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 15v2m0-8V7m0 0a4 4 0 018 0v4a4 4 0 01-4 4h-8a4 4 0 01-4-4V7a4 4 0 018 0z"/>
                                </svg>
                                Perbarui Password
                            </button>
                        </div>
                    </form>
                </div>

                {{-- INFO BANNER --}}
                <div class="flex items-start gap-4 rounded-3xl border border-blue-200 bg-blue-50 px-6 py-5 text-sm text-blue-800 shadow-sm">
                    <div class="bg-blue-100 p-2 rounded-full text-blue-600 flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M18 10A8 8 0 11.001 10 8 8 0 0118 10zm-8-3a1 1 0 10-2 0 1 1 0 002 0zm-1 2a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="leading-relaxed">Pastikan informasi akun yang Anda masukkan sudah benar. Data tertentu yang bersifat administratif dapat memerlukan verifikasi dari tim IT Bappelitbangda.</p>
                </div>
            </section>
        </div>
    </div>
</main>

{{-- ================= FOOTER ================= --}}
<footer class="bg-white border-t border-slate-200 mt-12">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-slate-500 font-medium">© {{ date('Y') }} Bappelitbangda · Booking Aula</p>
        <p class="text-sm text-slate-400">Sistem Booking Aula & Absensi</p>
    </div>
</footer>

{{-- ================= SWEETALERT NOTIFIKASI ================= --}}
@if($errors->has('current_password'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: 'error', title: 'Password Saat Ini Salah', text: @json($errors->first('current_password')), confirmButtonColor: '#dc2626', confirmButtonText: 'Coba Lagi' });
    });
</script>
@endif
@if($errors->has('password'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: 'error', title: 'Password Baru Bermasalah', text: @json($errors->first('password')), confirmButtonColor: '#dc2626', confirmButtonText: 'Coba Lagi' });
    });
</script>
@endif
@if(session('error_password'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: 'error', title: 'Gagal Memperbarui Password', text: @json(session('error_password')), confirmButtonColor: '#dc2626', confirmButtonText: 'Coba Lagi' });
    });
</script>
@endif
@if(session('status') === 'password-updated')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Password akun Anda telah berhasil diperbarui.', confirmButtonColor: '#059669', timer: 3000, timerProgressBar: true });
    });
</script>
@endif
@if(session('status') === 'profile-updated')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Profil Anda telah berhasil diperbarui.', confirmButtonColor: '#059669', timer: 3000, timerProgressBar: true });
    });
</script>
@endif
@if(session('status') === 'profile-photo-updated')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Foto profil Anda telah berhasil diperbarui.', confirmButtonColor: '#059669', timer: 3000, timerProgressBar: true });
    });
</script>
@endif

{{-- ================= ALPINE JS: FORM BIDANG & JABATAN ================= --}}
<script>
    function formBidang(initialBidang = '') {
        const listBidang = @json($bidangList ?? []);
        const isInList = listBidang.includes(initialBidang);
        return {
            selected: isInList ? initialBidang : (initialBidang ? '__lainnya__' : ''),
            customBidang: isInList ? '' : (initialBidang || ''),
            get isLainnya() { return this.selected === '__lainnya__'; },
            get finalBidang() { return this.isLainnya ? this.customBidang : this.selected; },
            handleChange() { if (!this.isLainnya) this.customBidang = ''; },
        };
    }
    function formJabatan(initialJabatan = '') {
        const listJabatan = @json($jabatanList ?? []);
        const isInList = listJabatan.includes(initialJabatan);
        return {
            selected: isInList ? initialJabatan : (initialJabatan ? '__lainnya__' : ''),
            customJabatan: isInList ? '' : (initialJabatan || ''),
            get isLainnya() { return this.selected === '__lainnya__'; },
            get finalJabatan() { return this.isLainnya ? this.customJabatan : this.selected; },
            handleChange() { if (!this.isLainnya) this.customJabatan = ''; },
        };
    }
</script>

<style>
    .rotate-anim { animation: spin-anim 15s linear infinite; }
    @keyframes spin-anim { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

@endsection