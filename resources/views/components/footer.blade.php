@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $isAdmin = $user && ($user->role === 'admin' || $user->is_admin === true);
@endphp

<footer class="bg-gradient-to-r from-green-500 via-teal-600 to-blue-600 dark:bg-black dark:from-black dark:via-neutral-950 dark:to-black dark:border-t dark:border-neutral-900 mt-auto text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- ═══════════════════════════════════════════ --}}
        {{-- BARIS UTAMA: Brand + Menu                        --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">

            {{-- KOLOM 1: BRAND --}}
            <div class="lg:col-span-1">
                <div class="mb-3 flex items-center gap-3">
                    <img src="{{ asset('image/logo.png') }}"
                         alt="Logo Pojok Bapelit"
                         class="h-12 w-12 rounded-xl object-cover shadow-sm ring-2 ring-white/30">
                    <h3 class="text-xl font-bold text-white">
                        Pojok Bapelit
                    </h3>
                </div>
              
                {{-- Tombol Tanya Asisten AI — di dalam kolom brand --}}
                <button type="button"
                        onclick="openChatbot()"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white transition hover:bg-white/20 hover:shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Tanya Admin asisten
                </button>
            </div>

            {{-- KOLOM 2: MENU ADMIN (khusus admin) --}}
            @auth
                @if($isAdmin)
                    <div>
                        <h3 class="text-sm font-semibold text-white uppercase tracking-wider">
                            Menu Admin
                        </h3>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li>
                                <a href="{{ route('admin.aula.index') }}"
                                   class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('admin.aula.*') ? 'text-white font-bold' : '' }}">
                                    Kelola Aula
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.kelolabooking.index') }}"
                                   class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('admin.kelolabooking.*') ? 'text-white font-bold' : '' }}">
                                    Kelola Booking
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.kelolasurat.index') }}"
                                   class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('admin.kelolasurat.*') ? 'text-white font-bold' : '' }}">
                                    Kelola Surat
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.kelolaakun.index') }}"
                                   class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('admin.kelolaakun.*') ? 'text-white font-bold' : '' }}">
                                    Kelola Akun
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.absensi.index') }}"
                                   class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('admin.absensi.*') ? 'text-white font-bold' : '' }}">
                                    Kelola Absensi
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
            @endauth

            {{-- KOLOM 3: MENU USER (semua role yang login) --}}
            @auth
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider">
                        Menu
                    </h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li>
                            <a href="{{ route('booking.index') }}"
                               class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('booking.*') ? 'text-white font-bold' : '' }}">
                                Booking Aula
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('riwayat.index') }}"
                               class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('riwayat.*') ? 'text-white font-bold' : '' }}">
                                Riwayat Booking
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('surat.index') }}"
                               class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('surat.*') ? 'text-white font-bold' : '' }}">
                                Ambil Surat
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('presensi.index') }}"
                               class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('presensi.*') ? 'text-white font-bold' : '' }}">
                                Presensi Saya
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('absensi.scan-page') }}"
                               class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('absensi.scan*') ? 'text-white font-bold' : '' }}">
                                Scan Absensi
                            </a>
                        </li>
                    </ul>
                </div>
            @endauth

            {{-- KOLOM 4: AKUN & BANTUAN --}}
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider">
                    Akun & Bantuan
                </h3>
                <ul class="mt-3 space-y-2 text-sm">

                    @auth
                        <li>
                            <a href="{{ route('akun.edit') }}"
                               class="inline-flex items-center gap-2 text-white/80 transition hover:text-white {{ request()->routeIs('akun.*') ? 'text-white font-bold' : '' }}">
                                Akun Profil
                            </a>
                        </li>
                        <li>
                            <button type="button" onclick="confirmLogout()"
                                    class="inline-flex items-center gap-2 text-red-200 transition hover:text-red-100 hover:underline">
                                Log Out
                            </button>
                        </li>
                    @endauth

                    <li>
                        <a href="#" class="inline-flex items-center gap-2 text-white/80 transition hover:text-white">
                            Bantuan
                        </a>
                    </li>

                </ul>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- BARIS BAWAH: Copyright                           --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="mt-8 border-t border-white/20 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-white/70">
                &copy; {{ date('Y') }} Bappeda. All rights reserved.
            </p>
            <p class="text-xs text-white/70">
                Pojok Bapelit v1.0.0
            </p>
        </div>

    </div>
</footer>