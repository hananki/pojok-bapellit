@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $currentRole = $user?->role ?? 'user';
    $isAdmin = $user && ($currentRole === 'admin' || $user->is_admin === true);

    // Menentukan Judul Menu berdasarkan Role
    $menuTitle = 'MENU USER';
    if ($isAdmin) {
        $menuTitle = 'MENU ADMIN';
    } elseif ($currentRole === 'pengelola_surat') {
        $menuTitle = 'MENU PENGELOLA SURAT';
    } elseif ($currentRole === 'pengelola_aula') {
        $menuTitle = 'MENU PENGELOLA AULA';
    } elseif ($currentRole === 'pengelola_absensi') {
        $menuTitle = 'MENU PENGELOLA ABSENSI';
    }
@endphp

<!-- WRAPPER: Mengatur Sembunyi/Munculnya Navbar -->
<div x-data="{ 
        showNavbar: true, 
        lastScrollY: 0 
    }" 
     x-init="
        lastScrollY = window.pageYOffset;
        window.addEventListener('scroll', () => {
            const currentScrollY = window.pageYOffset;
            
            // Jika di paling atas, selalu munculkan
            if (currentScrollY <= 10) {
                showNavbar = true;
            } 
            // Jika scroll ke bawah dan melewati 60px, sembunyikan
            else if (currentScrollY > lastScrollY && currentScrollY > 60) {
                showNavbar = false;
            } 
            // Jika scroll ke atas, munculkan
            else if (currentScrollY < lastScrollY) {
                showNavbar = true;
            }
            lastScrollY = currentScrollY;
        }, { passive: true });
     "
     :class="showNavbar ? 'translate-y-0' : '-translate-y-full'"
     class="sticky top-0 z-50 transition-transform duration-300 ease-in-out bg-white dark:bg-slate-900 shadow-md">

    <!-- NAVBAR UTAMA -->
    <nav class="bg-gradient-to-r from-green-500 via-teal-600 to-blue-600 dark:from-gray-950 dark:via-slate-900 dark:to-gray-950 dark:border-b dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">

                <!-- KIRI: Logo -->
                <div class="flex items-center">
                    <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('image/logo.png') }}"
                             alt="Logo Pojok Bapelit"
                             class="h-12 w-12 rounded-xl object-cover shadow-sm ring-2 ring-white/30">
                        <span class="text-xl font-bold text-white">Pojok Bapelit</span>
                    </a>
                </div>

                <!-- TENGAH: Menu Utama (HANYA DESKTOP) -->
                <div class="hidden md:flex md:items-center md:space-x-8">
                    <a href="{{ route('booking.index') }}" class="text-white/90 hover:text-white font-medium transition-colors {{ request()->routeIs('booking.*') ? 'text-white border-b-2 border-white pb-1' : '' }}">
                        Booking Aula
                    </a>
                    <a href="{{ route('riwayat.index') }}" class="text-white/90 hover:text-white font-medium transition-colors {{ request()->routeIs('riwayat.*') ? 'text-white border-b-2 border-white pb-1' : '' }}">
                        Riwayat Booking Aula
                    </a>
                    <a href="{{ route('surat.index') }}" class="text-white/90 hover:text-white font-medium transition-colors {{ request()->routeIs('surat.*') ? 'text-white border-b-2 border-white pb-1' : '' }}">
                        Ambil Surat
                    </a>
                    <a href="{{ route('presensi.index') }}" class="text-white/90 hover:text-white font-medium transition-colors {{ request()->routeIs('presensi.*') ? 'text-white border-b-2 border-white pb-1' : '' }}">
                        Presensi
                    </a>
                    <a href="{{ route('kendaraan.index') }}" class="text-white/90 hover:text-amber-300 font-medium transition-colors {{ request()->routeIs('kendaraan.*', 'admin.kendaraan.*') ? 'text-amber-300 border-b-2 border-amber-300 pb-1' : '' }}">
                        Pajak Kendaraan
                    </a>
                </div>

                <!-- KANAN: Theme Toggle + Notif + Dropdown Profil -->
                <div class="flex items-center gap-2">
                    @auth

                    {{-- Theme Toggle (Light/Dark) --}}
                    <button @click="toggleTheme()" 
                            x-data="{ isDark: document.documentElement.classList.contains('dark') }"
                            @theme-changed.window="isDark = $event.detail.isDark"
                            @storage.window="isDark = document.documentElement.classList.contains('dark')"
                            class="inline-flex items-center justify-center p-2 rounded-full text-white/80 hover:text-white hover:bg-black/10 focus:outline-none transition-colors"
                            title="Ganti Tema">
                        <svg x-show="!isDark" x-cloak class="w-5 h-5 text-amber-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg x-show="isDark" x-cloak class="w-5 h-5 text-blue-200" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>

                    {{-- Bell Notifikasi (Admin & User) --}}
                    @include('components.notification-bell')

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">

                        @if($isAdmin)
                            <button @click="open = !open"
                                class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-black/10 focus:outline-none transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                        @else
                            <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none py-2 px-3 rounded-md hover:bg-black/10 transition-colors">
                                <span class="text-sm font-medium text-white">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        @endif

                        <!-- DROPDOWN -->
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-64 rounded-xl shadow-xl bg-white dark:bg-slate-900 border border-transparent dark:border-slate-800 ring-1 ring-black ring-opacity-5 dark:ring-slate-800 z-50">
                            <div class="py-1">

                                <!-- Profile Info -->
                                <div class="px-4 py-2 text-sm text-gray-700 border-b">
                                    <div class="font-medium">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                    
                                    {{-- Badge Role Dinamis --}}
                                    @if($isAdmin)
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold text-white bg-red-600 rounded-full">Admin</span>
                                    @elseif($currentRole === 'pengelola_surat')
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold text-white bg-amber-500 rounded-full">Pengelola Surat</span>
                                    @elseif($currentRole === 'pengelola_aula')
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold text-white bg-indigo-500 rounded-full">Pengelola Aula</span>
                                    @elseif($currentRole === 'pengelola_absensi')
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold text-white bg-pink-500 rounded-full">Pengelola Absensi</span>
                                    @endif
                                </div>

                                <!-- MENU PANEL (HANYA UNTUK ADMIN & PENGELOLA) -->
                                @if(in_array($currentRole, ['admin', 'pengelola_surat', 'pengelola_aula', 'pengelola_absensi']))
                                    <div class="px-4 pt-2 pb-1 text-xs font-semibold text-teal-600 tracking-wider">{{ $menuTitle }}</div>

                                    {{-- Dashboard (Bisa dilihat semua staf) --}}
                                    <a href="{{ route('admin.dashboard.index') }}"
                                       class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                              {{ request()->routeIs('admin.dashboard.*')
                                                  ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                                  : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                        <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.dashboard.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4V5"></path>
                                        </svg>
                                        Dashboard
                                    </a>

                                    {{-- MENU PENGELOLA SURAT --}}
                                    @if(in_array($currentRole, ['admin', 'pengelola_surat']))
                                        <a href="{{ route('admin.kelolasurat.index') }}"
                                           class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                                  {{ request()->routeIs('admin.kelolasurat.*')
                                                      ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                                      : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                            <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.kelolasurat.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            Kelola Surat
                                        </a>
                                    @endif

                                    {{-- MENU PENGELOLA AULA --}}
                                    @if(in_array($currentRole, ['admin', 'pengelola_aula']))
                                        <a href="{{ route('admin.kelolabooking.index') }}"
                                           class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                                  {{ request()->routeIs('admin.kelolabooking.*')
                                                      ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                                      : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                            <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.kelolabooking.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Kelola Booking
                                        </a>
                                    @endif

                                    {{-- MENU PENGELOLA ABSENSI --}}
                                    @if(in_array($currentRole, ['admin', 'pengelola_absensi']))
                                        <a href="{{ route('admin.absensi.index') }}"
                                           class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                                  {{ request()->routeIs('admin.absensi.*')
                                                      ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                                      : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                            <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.absensi.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                            </svg>
                                            Kelola Absensi
                                        </a>
                                    @endif

                                    {{-- MENU MASTER (HANYA ADMIN) --}}
                                    @if($isAdmin)
                                        <a href="{{ route('admin.aula.index') }}"
                                           class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                                  {{ request()->routeIs('admin.aula.*')
                                                      ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                                      : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                            <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.aula.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            Kelola Aula
                                        </a>

                                        <a href="{{ route('admin.kelolaakun.index') }}"
                                           class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                                  {{ request()->routeIs('admin.kelolaakun.*')
                                                      ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                                      : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                            <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.kelolaakun.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            Kelola Akun
                                        </a>

                                        <a href="{{ route('admin.settings.index') }}"
                                           class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                                  {{ request()->routeIs('admin.settings.*')
                                                      ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                                      : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                            <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.settings.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            Pengaturan Sistem
                                        </a>
                                    @endif

                                    <div class="border-t my-1"></div>
                                @endif

                                <!-- MENU USER -->
                                <div class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-400 tracking-wider">MENU USER</div>

                                <a href="{{ route('booking.index') }}"
                                   class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                          {{ request()->routeIs('booking.*')
                                              ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                              : 'text-gray-700 border-transparent hover:bg-gray-50 hover:text-teal-600 hover:border-teal-600' }}">
                                    <svg class="w-4 h-4 transition-colors {{ request()->routeIs('booking.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Booking Aula
                                </a>

                                <a href="{{ route('riwayat.index') }}"
                                   class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                          {{ request()->routeIs('riwayat.*')
                                              ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                              : 'text-gray-700 border-transparent hover:bg-gray-50 hover:text-teal-600 hover:border-teal-600' }}">
                                    <svg class="w-4 h-4 transition-colors {{ request()->routeIs('riwayat.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Riwayat Booking
                                </a>

                                <a href="{{ route('surat.index') }}"
                                   class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                          {{ request()->routeIs('surat.*')
                                              ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                              : 'text-gray-700 border-transparent hover:bg-gray-50 hover:text-teal-600 hover:border-teal-600' }}">
                                    <svg class="w-4 h-4 transition-colors {{ request()->routeIs('surat.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Ambil Surat
                                </a>

                                <a href="{{ route('kendaraan.index') }}"
                                   class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                          {{ request()->routeIs('kendaraan.*', 'admin.kendaraan.*')
                                              ? 'bg-amber-50 text-amber-600 border-amber-600 font-semibold'
                                              : 'text-gray-700 border-transparent hover:bg-amber-50 hover:text-amber-600 hover:border-amber-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 17h14m-1 0a2 2 0 11-4 0m-6 0a2 2 0 11-4 0m1-9h8l3 4H5l1-4zm0 0H4a2 2 0 00-2 2v4h2m14 0h4v-3a1 1 0 00-1-1h-3m-8 0v-2h3v2" />
                                    </svg>
                                    Pajak Kendaraan
                                </a>

                                <a href="{{ route('presensi.index') }}"
                                   class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                          {{ request()->routeIs('presensi.*')
                                              ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                              : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                    <svg class="w-4 h-4 transition-colors {{ request()->routeIs('presensi.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Presensi Saya
                                </a>

                                {{-- Menu Scan Absensi --}}
                                <a href="{{ route('absensi.scan-page') }}"
                                   class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                          {{ request()->routeIs('absensi.scan*')
                                              ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                              : 'text-gray-700 border-transparent hover:bg-teal-50 hover:text-teal-600 hover:border-teal-600' }}">
                                    <svg class="w-4 h-4 transition-colors {{ request()->routeIs('absensi.scan*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                    </svg>
                                    Scan Absensi
                                </a>

                                {{-- AKUN --}}
                                <a href="{{ route('akun.edit') }}"
                                   class="group flex items-center gap-3 px-4 py-2.5 text-sm border-l-2 transition-all
                                          {{ request()->routeIs('akun.*')
                                              ? 'bg-teal-50 text-teal-600 border-teal-600 font-semibold'
                                              : 'text-gray-700 border-transparent hover:bg-gray-50 hover:text-teal-600 hover:border-teal-600' }}">
                                    <svg class="w-4 h-4 transition-colors
                                                {{ request()->routeIs('akun.*')
                                                    ? 'text-teal-600'
                                                    : 'text-gray-400 group-hover:text-teal-600' }}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Akun Profil
                                </a>

                                <div class="border-t my-1"></div>

                                <!-- LOGOUT -->
                                <form id="logout-form" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="button" onclick="confirmLogout()"
                                            class="group flex items-center gap-3 w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-all">
                                        <svg class="w-4 h-4 text-red-400 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endauth
                </div>

            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="bg-gradient-to-r from-green-500 to-blue-600 dark:from-gray-950 dark:to-slate-900 dark:border-b dark:border-slate-800 px-4 py-2 md:hidden">
        <a href="{{ route('kendaraan.index') }}" class="flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('kendaraan.*', 'admin.kendaraan.*') ? 'bg-black/20 text-white' : 'text-white/90 hover:bg-black/10 hover:text-white' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 17h14m-1 0a2 2 0 11-4 0m-6 0a2 2 0 11-4 0m1-9h8l3 4H5l1-4z" />
            </svg>
            Pajak Kendaraan
        </a>
    </div>

</div><!-- /x-data wrapper -->

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toggleTheme() {
        document.documentElement.classList.add('theme-transition');
        const isDark = document.documentElement.classList.contains('dark');
        const newTheme = isDark ? 'light' : 'dark';
        
        localStorage.setItem('theme', newTheme);
        document.documentElement.classList.toggle('dark', newTheme === 'dark');
        
        // Trigger Alpine re-render & custom event
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: newTheme, isDark: newTheme === 'dark' } }));
        window.dispatchEvent(new Event('storage'));

        setTimeout(() => {
            document.documentElement.classList.remove('theme-transition');
        }, 350);
    }

    function confirmLogout() {
        Swal.fire({
            title: 'Yakin ingin log out?',
            text: "Sesi Anda akan diakhiri.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Log out!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        })
    }

    document.addEventListener('DOMContentLoaded', () => {
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.classList.toggle('dark', theme === 'dark');
    });
</script>