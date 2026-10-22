@php
    /** @var \App\Models\User|null $userAuth */
    $userAuth = Auth::user();
    $isAdmin = $userAuth?->isAdmin() ?? false;
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.user';
    
    $isEdit = $user !== null;
    $title = $isEdit ? 'Edit Akun' : 'Tambah Akun Baru';
    $subtitle = $isEdit 
        ? 'Ubah data pengguna dan hak akses mereka di dalam sistem.' 
        : 'Tambahkan pengguna baru dan atur hak akses mereka di dalam sistem.';
    $action = $isEdit 
        ? route('admin.kelolaakun.update', $user->id) 
        : route('admin.kelolaakun.store');
@endphp

@extends($layout)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <!-- Header -->
    <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-10">
        <a href="{{ route('admin.kelolaakun.index') }}" class="p-2.5 sm:p-3 bg-white rounded-full shadow-sm hover:bg-gray-100 transition-colors shrink-0">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-3xl md:text-4xl font-bold text-gray-800 leading-tight">{{ $title }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
        </div>
    </div>

    <!-- Card Form -->
    <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-8 md:p-10 border border-gray-100" 
         x-data="formAkun('{{ old('bidang', $user->bidang ?? '') }}')">
        
        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-base">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ $action }}" method="POST">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- ========== SECTION 1: DATA PRIBADI ========== -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    Data Pribadi
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Lengkap -->
                    <div class="md:col-span-2">
                        <label class="block text-base font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" 
                               value="{{ old('name', $user->name ?? '') }}" 
                               placeholder="Masukkan nama lengkap"
                               class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                        @error('name') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- NIP -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">NIP / NI PPPK</label>
                        <input type="text" name="nip" 
                               value="{{ old('nip', $user->nip ?? '') }}" 
                               placeholder="Contoh: 19780712 200701 2 016"
                               class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                        @error('nip') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- No WhatsApp -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">No. WhatsApp</label>
                        <input type="text" name="whatsapp" 
                               value="{{ old('whatsapp', $user->whatsapp ?? '') }}" 
                               placeholder="0812xxxxxxx"
                               class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                        @error('whatsapp') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- ========== SECTION 2: DATA KEPEGAWAIAN ========== -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    Data Kepegawaian
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Jabatan -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">
                            Jabatan <span class="text-sm text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input type="text" name="jabatan" 
                               value="{{ old('jabatan', $user->jabatan ?? '') }}" 
                               placeholder="Contoh: Kepala Bidang, Perencana Ahli Muda"
                               class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                        @error('jabatan') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Golongan -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">Golongan</label>
                        <input type="text" name="golongan" 
                               value="{{ old('golongan', $user->golongan ?? '') }}" 
                               placeholder="Contoh: IV/c, III/d, IX"
                               class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                        @error('golongan') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Bidang (Dropdown + Manual Input) â€” OPSIONAL -->
                    <div class="md:col-span-2">
                        <label class="block text-base font-medium text-gray-700 mb-2">
                            Asal Bidang / Bagian <span class="text-sm text-gray-400 font-normal">(opsional)</span>
                        </label>
                        
                        {{-- Dropdown Bidang --}}
                        <select x-model="selected" 
                                @change="handleChange()"
                                class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                            <option value="">-- Pilih Bidang --</option>
                            @foreach(($bidangList ?? collect()) as $bidang)
                                <option value="{{ $bidang }}">{{ ucwords(strtolower($bidang)) }}</option>
                            @endforeach
                            <option value="__lainnya__">Lainnya (isi manual)...</option>
                        </select>

                        {{-- Input Manual (muncul kalau pilih "Lainnya") --}}
                        <div x-show="isLainnya" x-cloak class="mt-3">
                            <input type="text" 
                                   x-model="customBidang"
                                   placeholder="Ketik nama bidang manual..."
                                   class="w-full border border-blue-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base bg-blue-50/30">
                            <p class="text-sm text-gray-500 mt-1.5">Isi nama bidang dengan lengkap</p>
                        </div>

                        {{-- Hidden input yang dikirim ke controller --}}
                        <input type="hidden" name="bidang" :value="finalBidang">

                        @error('bidang') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- ========== SECTION 3: AKUN & HAK AKSES ========== -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    Akun & Hak Akses
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" 
                               value="{{ old('username', $user->username ?? '') }}" 
                               placeholder="Kosongkan jika pegawai tidak perlu login"
                               class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                        @error('username') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">Email Aktif</label>
                        <input type="email" name="email" 
                               value="{{ old('email', $user->email ?? '') }}" 
                               placeholder="example@domain.com"
                               class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                        @error('email') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">
                            Peran / Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role" 
                                class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                            <option value="" disabled {{ old('role', $user->role ?? '') == '' ? 'selected' : '' }}>-- Pilih Role --</option>
                            <option value="user" {{ old('role', $user->role ?? '') == 'user' ? 'selected' : '' }}>User / Pegawai</option>
                            <option value="pengelola_surat" {{ old('role', $user->role ?? '') == 'pengelola_surat' ? 'selected' : '' }}>Pengelola Surat</option>
                            <option value="pengelola_aula" {{ old('role', $user->role ?? '') == 'pengelola_aula' ? 'selected' : '' }}>Pengelola Aula (Booking)</option>
                            <option value="pengelola_absensi" {{ old('role', $user->role ?? '') == 'pengelola_absensi' ? 'selected' : '' }}>Pengelola Absensi</option>
                            <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Admin (Akses Penuh)</option>
                        </select>
                        @error('role') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">
                            Status Akun <span class="text-red-500">*</span>
                        </label>
                        <select name="status" 
                                class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 text-base">
                            <option value="aktif"    {{ old('status', $user->status ?? 'aktif') == 'aktif'    ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status', $user->status ?? '') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Info: Login Opsional -->
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Pegawai tanpa akun login?</p>
                            <p>Kosongkan <strong>username</strong>, <strong>email</strong>, dan <strong>password</strong> jika pegawai hanya untuk diabsen dan tidak perlu login ke sistem.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== SECTION 4: PASSWORD ========== -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    Password
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">
                            Password
                            @if($isEdit)
                                <span class="text-sm text-gray-400 font-normal">(kosongkan jika tidak ingin ubah)</span>
                            @else
                                <span class="text-sm text-gray-400 font-normal">(kosongkan jika pegawai tanpa akun)</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password"
                                   name="password" 
                                   placeholder="Minimal 8 karakter"
                                   class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 pr-12 text-base">
                            <button type="button" 
                                    onclick="togglePassword('password', 'eye-open-1', 'eye-closed-1')"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                <svg id="eye-open-1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-closed-1" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <input type="password" 
                                   id="password_confirmation"
                                   name="password_confirmation" 
                                   placeholder="Ulangi password"
                                   class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3.5 pr-12 text-base">
                            <button type="button" 
                                    onclick="togglePassword('password_confirmation', 'eye-open-2', 'eye-closed-2')"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                <svg id="eye-open-2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-closed-2" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100 pt-6">
                <a href="{{ route('admin.kelolaakun.index') }}" 
                   class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium text-sm text-center hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm">
                    {{ $isEdit ? 'Update Akun' : 'Simpan Akun' }}
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    // ===== Toggle Password Visibility =====
    function togglePassword(inputId, eyeOpenId, eyeClosedId) {
        const input = document.getElementById(inputId);
        const eyeOpen = document.getElementById(eyeOpenId);
        const eyeClosed = document.getElementById(eyeClosedId);

        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }

    // ===== Bidang Manager (Dropdown + Manual Input) =====
    function formAkun(initialBidang = '') {
        // Daftar bidang â€” HURUF BESAR, sinkron dengan <option value>
        const listBidang = [
            'KEPALA BADAN',
            'SEKRETARIAT',
            'BIDANG PEREKONOMIAN DAN SUMBER DAYA ALAM',
            'BIDANG INFRASTRUKTUR DAN KEWILAYAHAN',
            'BIDANG PEMERINTAHAN DAN PEMBANGUNAN MANUSIA',
            'BIDANG PERENCANAAN PENGENDALIAN DAN EVALUASI',
            'BIDANG PENELITIAN DAN PENGEMBANGAN',
            'IT',
        ];

        const isInList = listBidang.includes(initialBidang);

        return {
            selected: isInList ? initialBidang : (initialBidang ? '__lainnya__' : ''),
            customBidang: isInList ? '' : (initialBidang || ''),

            get isLainnya() {
                return this.selected === '__lainnya__';
            },

            get finalBidang() {
                return this.isLainnya ? this.customBidang : this.selected;
            },

            handleChange() {
                if (!this.isLainnya) {
                    this.customBidang = '';
                }
            },
        };
    }
</script>
@endsection