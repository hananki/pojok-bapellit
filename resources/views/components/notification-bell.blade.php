<div
    x-data="notifBell()"
    x-init="init()"
    class="relative"
>
    {{-- Tombol Bell --}}
    <button @click="toggle()"
            type="button"
            class="relative p-2 text-white/80 hover:text-white hover:bg-black/10 rounded-full focus:outline-none transition-colors"
            title="Notifikasi">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        <span x-show="unreadCount > 0"
              x-cloak
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute top-0 right-0 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full ring-2 ring-white">
        </span>

        <span x-show="unreadCount > 0"
              x-cloak
              class="absolute top-0 right-0 inline-flex h-[18px] w-[18px] rounded-full bg-red-400 opacity-75 animate-ping">
        </span>
    </button>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MOBILE: Overlay + Panel Full Width                           --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="sm:hidden fixed inset-0 bg-black/40 z-[9998]">
    </div>

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="sm:hidden fixed z-[9999] bg-white rounded-t-2xl shadow-2xl flex flex-col"
         style="top: 4rem; left: 0.5rem; right: 0.5rem; max-height: calc(100vh - 5rem);">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50 rounded-t-2xl flex-shrink-0">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-800">Notifikasi</span>
                <span x-show="unreadCount > 0"
                      x-cloak
                      x-text="'(' + unreadCount + ' baru)'"
                      class="text-xs text-red-600 font-medium"></span>
            </div>
            <button @click="open = false"
                    type="button"
                    class="p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-colors"
                    title="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Aksi --}}
        <div class="flex items-center justify-between px-4 py-2 border-b bg-white flex-shrink-0">
            <button @click="toggleSound()"
                    type="button"
                    class="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-gray-900">
                <svg x-show="soundEnabled" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                </svg>
                <svg x-show="!soundEnabled" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                </svg>
                <span x-text="soundEnabled ? 'Suara aktif' : 'Suara mati'"></span>
            </button>

            <button @click="markAllRead()"
                    type="button"
                    x-show="unreadCount > 0"
                    x-cloak
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                Tandai semua
            </button>
        </div>

        {{-- Daftar Notifikasi (grouped) --}}
        <div class="flex-1 overflow-y-auto">
            <template x-for="group in groupedNotifications" :key="group.label">
                <div>
                    {{-- Group Label --}}
                    <div class="sticky top-0 z-10 px-4 py-1.5 bg-gray-50/95 backdrop-blur-sm border-y border-gray-100">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider"
                              x-text="group.label"></span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <template x-for="n in group.items" :key="n.id">
                            <a :href="n.url || '#'"
                               @click.prevent="openNotif(n)"
                               class="block px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
                               :class="!n.is_read ? 'bg-blue-50/50' : ''">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                                         :class="{
                                             'bg-indigo-100 text-indigo-600': n.type === 'booking',
                                             'bg-amber-100 text-amber-600': n.type === 'surat',
                                             'bg-pink-100 text-pink-600': n.type === 'absensi',
                                             'bg-gray-100 text-gray-600': !['booking','surat','absensi'].includes(n.type)
                                         }">
                                        <svg x-show="n.type === 'booking'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <svg x-show="n.type === 'surat'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <svg x-show="n.type === 'absensi'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <svg x-show="!['booking','surat','absensi'].includes(n.type)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-sm text-gray-800 truncate" x-text="n.title"></span>
                                            <span x-show="!n.is_read" class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full"></span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-0.5 line-clamp-2" x-text="n.message"></p>
                                        <p x-show="n.data && n.data.tanggal"
                                           class="text-[11px] text-indigo-600 font-medium mt-1"
                                           x-text="'📅 ' + n.data.tanggal + (n.data.sesi ? ' • ' + n.data.sesi : '')"></p>
                                        <p class="text-[11px] text-gray-400 mt-1" x-text="formatTime(n.created_at)"></p>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="notifications.length === 0" class="px-4 py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm text-gray-400 mt-2">Belum ada notifikasi</p>
            </div>
        </div>

        {{-- Status --}}
        <div class="px-4 py-2 border-t bg-gray-50 text-center flex items-center justify-center gap-2 rounded-b-2xl flex-shrink-0">
            <span class="inline-block w-2 h-2 rounded-full"
                  :class="connected ? 'bg-green-500' : 'bg-gray-300'"></span>
            <span class="text-xs text-gray-500"
                  x-text="connected ? 'Realtime aktif' : 'Menghubungkan...'"></span>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- DESKTOP: Dropdown Compact                                    --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div x-show="open"
         x-cloak
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="hidden sm:block absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-[9999] overflow-hidden">

        <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-800">Notifikasi</span>
                <span x-show="unreadCount > 0"
                      x-cloak
                      x-text="'(' + unreadCount + ' baru)'"
                      class="text-xs text-red-600 font-medium"></span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="toggleSound()"
                        type="button"
                        :title="soundEnabled ? 'Matikan suara' : 'Nyalakan suara'"
                        class="text-gray-500 hover:text-gray-700">
                    <svg x-show="soundEnabled" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                    </svg>
                    <svg x-show="!soundEnabled" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                </button>

                <button @click="markAllRead()"
                        type="button"
                        x-show="unreadCount > 0"
                        x-cloak
                        class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline font-medium">
                    Tandai semua
                </button>
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <template x-for="group in groupedNotifications" :key="group.label">
                <div>
                    {{-- Group Label --}}
                    <div class="sticky top-0 z-10 px-4 py-1.5 bg-gray-50/95 backdrop-blur-sm border-y border-gray-100">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider"
                              x-text="group.label"></span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <template x-for="n in group.items" :key="n.id">
                            <a :href="n.url || '#'"
                               @click.prevent="openNotif(n)"
                               class="block px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
                               :class="!n.is_read ? 'bg-blue-50/50' : ''">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                                         :class="{
                                             'bg-indigo-100 text-indigo-600': n.type === 'booking',
                                             'bg-amber-100 text-amber-600': n.type === 'surat',
                                             'bg-pink-100 text-pink-600': n.type === 'absensi',
                                             'bg-gray-100 text-gray-600': !['booking','surat','absensi'].includes(n.type)
                                         }">
                                        <svg x-show="n.type === 'booking'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <svg x-show="n.type === 'surat'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <svg x-show="n.type === 'absensi'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <svg x-show="!['booking','surat','absensi'].includes(n.type)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-sm text-gray-800 truncate" x-text="n.title"></span>
                                            <span x-show="!n.is_read" class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full"></span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-0.5 line-clamp-2" x-text="n.message"></p>
                                        <p x-show="n.data && n.data.tanggal"
                                           class="text-[11px] text-indigo-600 font-medium mt-1"
                                           x-text="'📅 ' + n.data.tanggal + (n.data.sesi ? ' • ' + n.data.sesi : '')"></p>
                                        <p class="text-[11px] text-gray-400 mt-1" x-text="formatTime(n.created_at)"></p>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="notifications.length === 0" class="px-4 py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm text-gray-400 mt-2">Belum ada notifikasi</p>
            </div>
        </div>

        <div class="px-4 py-2 border-t bg-gray-50 text-center flex items-center justify-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full"
                  :class="connected ? 'bg-green-500' : 'bg-gray-300'"></span>
            <span class="text-xs text-gray-500"
                  x-text="connected ? 'Realtime aktif' : 'Menghubungkan...'"></span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- Alpine Component + Suara + Grouping                          --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<script>
function notifBell() {
    let audio = null;
    let audioContext = null;
    let audioGain = null;
    let lastFetch = 0;
    let stream = null;
    let lastPlayTime = 0;   // ⬅️ cegah double play

    return {
        open: false,
        notifications: [],
        unreadCount: {{ auth()->user()->unreadNotificationsCount() ?? 0 }},
        connected: false,
        soundEnabled: true,
        audioUrl: '{{ \App\Models\Setting::getNotificationSound() }}',
        _initialized: false,

        init() {
            if (this._initialized) return;
            this._initialized = true;

            this.setupAudio();
            this.fetchNotifs();

            if (!window.NotificationStream) {
                console.error('❌ NotificationStream belum ke-load. Cek app.js');
                return;
            }

            stream = new window.NotificationStream({
                streamUrl: '{{ route('notifications.stream') }}',

                // 🔊 CUMA DI SINI suara dibunyikan — kecuali tipe yang di-silent
                onNotification: (notif) => {
                    // 🛡️ LAPIS PENGAMAN: Abaikan jika tipe notif bukan hak role ini
                    const role = window.authUserRole || 'user';
                    const type = notif.type || '';
                    
                    if (role !== 'admin') {
                        if (role === 'pengelola_surat' && type !== 'surat') return;
                        if (role === 'pengelola_aula' && type !== 'booking') return;
                        if (role === 'pengelola_absensi' && type !== 'absensi') return;
                    }
                    if (role === 'user' && !['booking', 'surat', 'absensi'].includes(type)) return;

                    // --- Jika lolos filter, jalankan seperti biasa ---
                    console.log('🔔 Notif SSE masuk:', notif);
                    this.notifications.unshift(notif);
                    this.unreadCount++;
                    
                    const silentTypes = ['session_banned'];
                    if (!silentTypes.includes(notif.type)) {
                        this.playSound();
                    }
                    this.showToast(notif);
                },

                onStatusChange: (ok) => { this.connected = ok; },

                // Resync JANGAN bunyi
                onResync: () => this.resync(),
            });

            stream.connect();
        },

        destroy() {
            if (stream) {
                stream.disconnect();
                stream = null;
            }
        },

        // ═══════════════════════════════════════════════════════
        // GROUPING — hari ini, kemarin, minggu lalu, bulan lalu
        // ═══════════════════════════════════════════════════════
        get groupedNotifications() {
            const now = new Date();
            const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const startOfYesterday = new Date(startOfToday);
            startOfYesterday.setDate(startOfYesterday.getDate() - 1);
            const startOfWeek = new Date(startOfToday);
            startOfWeek.setDate(startOfWeek.getDate() - 7);
            const startOfMonth = new Date(startOfToday);
            startOfMonth.setDate(startOfMonth.getDate() - 30);

            const groups = {
                'Hari Ini': [],
                'Kemarin': [],
                'Minggu Lalu': [],
                'Bulan Lalu': [],
                'Lebih Lama': [],
            };

            this.notifications.forEach(n => {
                const d = new Date(n.created_at);
                if (isNaN(d.getTime())) {
                    groups['Lebih Lama'].push(n);
                    return;
                }
                if (d >= startOfToday)          groups['Hari Ini'].push(n);
                else if (d >= startOfYesterday) groups['Kemarin'].push(n);
                else if (d >= startOfWeek)      groups['Minggu Lalu'].push(n);
                else if (d >= startOfMonth)     groups['Bulan Lalu'].push(n);
                else                            groups['Lebih Lama'].push(n);
            });

            return Object.entries(groups)
                .filter(([, items]) => items.length > 0)
                .map(([label, items]) => ({ label, items }));
        },

        // ═══════════════════════════════════════════════════════
        // AUDIO — cuma prepare, GAK bunyi saat unlock
        // ═══════════════════════════════════════════════════════
        setupAudio() {
            const saved = localStorage.getItem('notification_sound_enabled');
            if (saved !== null) this.soundEnabled = saved === 'true';

            audio = new Audio(this.audioUrl);
            audio.preload = 'auto';
            audio.volume = 1.0;

            // Perkuat suara custom/default melalui Web Audio API.
            // Gain 2.5 membuat file custom yang pelan terdengar lebih jelas.
            try {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const source = audioContext.createMediaElementSource(audio);
                audioGain = audioContext.createGain();
                audioGain.gain.value = 2.5;
                source.connect(audioGain);
                audioGain.connect(audioContext.destination);
            } catch (error) {
                console.warn('[Sound] Amplifier tidak tersedia, memakai volume normal:', error);
            }

            // Unlock audio tanpa menghasilkan suara.
            const events = ['click', 'keydown', 'touchstart'];
            const unlock = () => {
                if (audioContext?.state === 'suspended') audioContext.resume();
                audio.volume = 0;
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                    audio.volume = 1.0;
                    events.forEach(ev => document.removeEventListener(ev, unlock));
                    console.log('[Sound] Audio unlocked');
                }).catch(() => {
                    audio.volume = 1.0;
                });
            };
            events.forEach(ev => document.addEventListener(ev, unlock, { once: true }));
        },

        // ═══════════════════════════════════════════════════════
        // PLAY SOUND — cuma dipanggil dari onNotification
        // ═══════════════════════════════════════════════════════
        playSound() {
            if (!audio || !this.soundEnabled) return;

            const now = Date.now();
            if (now - lastPlayTime < 800) {
                console.log('[Sound] Skip — terlalu cepat');
                return;
            }
            lastPlayTime = now;

            if (audioContext?.state === 'suspended') audioContext.resume();
            audio.currentTime = 0;
            audio.volume = 1.0;
            audio.play()
                .then(() => console.log('[Sound] 🔊 Bunyi diperkuat (notif baru)'))
                .catch((err) => console.warn('[Sound] Gagal play:', err.name));
        },

        // Toggle suara. Saat unmute, putar preview satu kali agar pengguna mengetahui volumenya.
        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            localStorage.setItem('notification_sound_enabled', this.soundEnabled);

            if (this.soundEnabled) {
                this.playSoundPreview();
            }
        },

        playSoundPreview() {
            if (!audio) return;

            const now = Date.now();
            if (now - lastPlayTime < 800) return;
            lastPlayTime = now;

            if (audioContext?.state === 'suspended') {
                audioContext.resume();
            }

            audio.pause();
            audio.currentTime = 0;
            audio.volume = 1.0;
            audio.play()
                .then(() => console.log('[Sound] Preview unmute diputar'))
                .catch((error) => console.warn('[Sound] Preview gagal diputar:', error.name));
        },

        showToast(notif) {
            if (!window.Swal) return;
            const icon = { booking: 'info', surat: 'success', absensi: 'info' }[notif.type] || 'info';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: notif.title,
                text: notif.message,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
            });
        },

        async fetchNotifs(force = false) {
            const now = Date.now();
            if (!force && now - lastFetch < 2000) return;
            lastFetch = now;

            try {
                const res = await fetch('{{ route('notifications.index') }}', {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);

                const data = await res.json();
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
            } catch (e) {
                console.error('❌ Gagal fetch notif:', e);
            }
        },

        // Resync JANGAN bunyi — cuma refresh list
        async resync() {
            await this.fetchNotifs(true);
        },

        toggle() {
            this.open = !this.open;
            if (this.open) this.fetchNotifs();

            if (this.open && window.innerWidth < 640) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        },

        async openNotif(n) {
            if (!n.is_read) {
                try {
                    const res = await fetch(`/notifications/${n.id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    if (res.ok) {
                        n.is_read = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                } catch (e) {
                    console.error('❌ Gagal tandai baca:', e);
                }
            }
            if (n.url) window.location.href = n.url;
        },

        async markAllRead() {
            try {
                const res = await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                if (res.ok) {
                    this.notifications.forEach(n => n.is_read = true);
                    this.unreadCount = 0;
                }
            } catch (e) {
                console.error('❌ Gagal tandai semua:', e);
            }
        },

        async deleteNotification(id) {
            try {
                const res = await fetch(`/notifications/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                if (res.ok) {
                    // Remove dari array
                    this.notifications = this.notifications.filter(n => n.id !== id);
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                    console.log('[Notif] Deleted notification:', id);
                }
            } catch (e) {
                console.error('❌ Gagal hapus notif:', e);
            }
        },

        formatTime(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            if (isNaN(d.getTime())) return '';
            const diff = Math.floor((new Date() - d) / 1000);
            if (diff < 60) return 'Baru saja';
            if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
            return d.toLocaleDateString('id-ID');
        },
    }
}
</script>