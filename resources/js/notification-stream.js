    /**
     * NotificationStream — klien SSE untuk notifikasi realtime.
     *
     * - Guard duplikat berbasis ID string (bukan perbandingan objek), aman dari
     *   Proxy milik Alpine.
     * - Reconnect bawaan browser dibiarkan jalan (Last-Event-ID tetap terkirim);
     *   reconnect manual hanya kalau koneksi benar-benar CLOSED.
     * - SSE ditutup saat pindah halaman (pagehide) supaya thread server cepat lepas.
     * - Tab di background TIDAK ditutup secara default (hiddenGraceMs = Infinity),
     *   jadi notifikasi + suara tetap masuk saat admin sedang di jendela lain.
     * - Resync lewat onResync / event `realtime:resync` setelah koneksi putus lama.
     */
    class NotificationStream {
        constructor(options = {}) {
            this.streamUrl           = options.streamUrl || '/notifications/stream';
            this.onNotification      = options.onNotification || (() => {});
            this.onUnreadCountChange = options.onUnreadCountChange || (() => {});
            this.onDataChanged       = options.onDataChanged || null;
            this.onResync            = options.onResync || (() => {});
            this.onStatusChange      = options.onStatusChange || (() => {});

            // Tab tersembunyi lebih lama dari ini (ms) → SSE ditutup.
            // Default Infinity = tidak pernah ditutup (notifikasi tetap masuk di background).
            this.hiddenGraceMs = options.hiddenGraceMs ?? Infinity;
            // Kalau koneksi putus lebih lama dari ini, minta UI resync saat tersambung lagi.
            this.resyncAfterMs = options.resyncAfterMs ?? 10000;  // naik dari 5s

            // ID stabil untuk guard kepemilikan. Jangan bandingkan `this` langsung:
            // Alpine membungkus objek di state reaktif dengan Proxy, sehingga `this`
            // bisa berupa Proxy di satu tempat dan objek asli di tempat lain.
            this._id = 'ns-' + Math.random().toString(36).slice(2);

            this.unreadCount = options.initialUnreadCount || 0;
            this.eventSource = null;
            this.connected   = false;

            this.reconnectDelay        = 5000;   // 5 detik (naik dari 3s)
            this.maxReconnectDelay     = 60000;  // 60 detik (naik dari 30s)
            this.currentReconnectDelay = this.reconnectDelay;

            this._reconnectTimer = null;
            this._hiddenTimer    = null;
            this._wantConnected  = false;
            this._lifecycleBound = false;
            this._dropAt         = null;
            this._needsResync    = false;

            this._onVisibility = () => {
                if (!this._wantConnected) return;

                if (document.hidden) {
                    if (this._hiddenTimer || !Number.isFinite(this.hiddenGraceMs)) return;
                    this._hiddenTimer = setTimeout(() => {
                        this._hiddenTimer = null;
                        if (document.hidden && this.eventSource) {
                            console.log('[SSE] Tab tersembunyi — tutup koneksi');
                            this._needsResync = true;
                            this._close();
                        }
                    }, this.hiddenGraceMs);
                } else {
                    this._clearHiddenTimer();
                    if (!this.eventSource) this._open();
                }
            };

            this._onPageHide = () => {
                if (!this._wantConnected) return;
                this._needsResync = true;
                this._close(); // lepas thread server secepat mungkin saat pindah halaman
            };

            this._onPageShow = (e) => {
                // Halaman dipulihkan dari bfcache (tombol back/forward)
                if (e.persisted && this._wantConnected && !document.hidden && !this.eventSource) {
                    this._open();
                }
            };
        }

        // ------------------------------------------------------------------ API

        connect() {
            this._wantConnected = true;
            this._bindLifecycle();

            // Tab tersembunyi: tunggu sampai terlihat baru buka koneksi.
            if (document.hidden) return;

            this._open();
        }

        disconnect() {
            this._wantConnected = false;
            this._unbindLifecycle();
            this._clearHiddenTimer();
            this._close();
        }

        setUnreadCount(count) {
            this.unreadCount = count;
            this.onUnreadCountChange(count);
        }

        // ------------------------------------------------------------ internals

        _open() {
            if (!this._wantConnected) return;

            // Guard duplikat: instance lain di halaman ini sudah pegang SSE → skip.
            const owner = window.__notifStreamOwner;
            if (owner && owner !== this._id) {
                console.warn('[SSE] Sudah ada instance aktif — skip duplikat');
                return;
            }

            // Sudah ada koneksi yang hidup / sedang menyambung → jangan buat baru.
            if (this.eventSource && this.eventSource.readyState !== EventSource.CLOSED) {
                return;
            }

            this._clearReconnectTimer();
            if (this.eventSource) this.eventSource.close();

            const es = new EventSource(this.streamUrl, { withCredentials: true });
            this.eventSource = es;
            window.__notifStreamOwner = this._id;

            es.onopen = () => {
                if (es !== this.eventSource) return;

                console.log('[SSE] Connected');
                this.connected = true;
                this.onStatusChange(true);
                this.currentReconnectDelay = this.reconnectDelay;

                const gap = this._dropAt ? Date.now() - this._dropAt : 0;
                if (this._needsResync || gap > this.resyncAfterMs) {
                    this._resync();
                }
                this._needsResync = false;
                this._dropAt = null;
            };

                    es.addEventListener('notification', (e) => {
                if (es !== this.eventSource) return;
                try {
                    const notif = JSON.parse(e.data);

                    // ===== FILTER BERDASARKAN ROLE =====
                    const userRole = window.authUserRole || document.querySelector('meta[name="user-role"]')?.content || 'user';
                    const notifType = notif.type || ''; // Mengambil tipe dari payload

                    let isAllowed = false;
                    if (userRole === 'admin') {
                        isAllowed = true;
                    } else if (userRole === 'pengelola_surat' && notifType === 'surat') {
                        isAllowed = true;
                    } else if (userRole === 'pengelola_aula' && notifType === 'booking') {
                        isAllowed = true;
                    } else if (userRole === 'pengelola_absensi' && notifType === 'absensi') {
                        isAllowed = true;
                    } else if (userRole === 'user' && ['booking', 'surat', 'presensi'].includes(notifType)) {
                        isAllowed = true;
                    }

                    // Jika tidak berhak, abaikan sinyal ini (jangan naikkan unreadCount)
                    if (!isAllowed) return;

                    // ===== JIKA BERHAK, JALANKAN =====
                    this.unreadCount++;
                    this.onUnreadCountChange(this.unreadCount);
                    this.onNotification(notif);

                    window.dispatchEvent(new CustomEvent('realtime:notification', {
                        detail: notif,
                    }));
                } catch (err) {
                    console.error('[SSE] Gagal parse notif:', err);
                }
            });

            es.addEventListener('data-changed', (e) => {
                if (es !== this.eventSource) return;
                try {
                    const payload = JSON.parse(e.data);
                    window.dispatchEvent(new CustomEvent('realtime:data-changed', {
                        detail: payload,
                    }));
                    if (typeof this.onDataChanged === 'function') {
                        this.onDataChanged(payload);
                    }
                } catch (err) {
                    console.error('[SSE] Gagal parse data-changed:', err);
                }
            });

            // Server menutup stream sesuai jadwal (maxDuration). Browser akan
            // reconnect sendiri (dengan Last-Event-ID) — jangan di-close manual.
            es.addEventListener('reconnect', () => {
                console.log('[SSE] Server menutup stream terjadwal, browser reconnect otomatis');
            });

            es.onerror = () => {
                if (es !== this.eventSource) return; // event dari koneksi lama

                this.connected = false;
                this.onStatusChange(false);
                if (this._dropAt === null) this._dropAt = Date.now();

                // CONNECTING → browser sedang reconnect sendiri, biarkan.
                // CLOSED     → browser menyerah (mis. HTTP 401/419/500) → reconnect manual.
                if (es.readyState === EventSource.CLOSED) {
                    this.eventSource = null;
                    this._releaseOwnership();
                    this._scheduleReconnect();
                }
            };
        }

        _close() {
            this._clearReconnectTimer();
            if (this.eventSource) {
                this.eventSource.close();
                this.eventSource = null;
            }
            if (this.connected) {
                this.connected = false;
                this.onStatusChange(false);
            }
            this._releaseOwnership();
        }

        _releaseOwnership() {
            if (window.__notifStreamOwner === this._id) {
                window.__notifStreamOwner = null;
            }
        }

        _scheduleReconnect() {
            if (this._reconnectTimer || !this._wantConnected) return;
            // Tab tersembunyi: tunggu sampai terlihat (ditangani _onVisibility).
            if (document.hidden) return;

            const jitter = Math.floor(Math.random() * 500);
            const delay  = this.currentReconnectDelay + jitter;

            console.log(`[SSE] Reconnect dalam ${delay}ms`);

            this._reconnectTimer = setTimeout(() => {
                this._reconnectTimer = null;
                this._open();
            }, delay);

            this.currentReconnectDelay = Math.min(
                this.currentReconnectDelay * 2,
                this.maxReconnectDelay
            );
        }

        _resync() {
            try {
                this.onResync();
            } catch (err) {
                console.error('[SSE] onResync error:', err);
            }
            window.dispatchEvent(new CustomEvent('realtime:resync'));
        }

        _clearReconnectTimer() {
            if (this._reconnectTimer) {
                clearTimeout(this._reconnectTimer);
                this._reconnectTimer = null;
            }
        }

        _clearHiddenTimer() {
            if (this._hiddenTimer) {
                clearTimeout(this._hiddenTimer);
                this._hiddenTimer = null;
            }
        }

        _bindLifecycle() {
            if (this._lifecycleBound) return;
            document.addEventListener('visibilitychange', this._onVisibility);
            window.addEventListener('pagehide', this._onPageHide);
            window.addEventListener('pageshow', this._onPageShow);
            this._lifecycleBound = true;
        }

        _unbindLifecycle() {
            if (!this._lifecycleBound) return;
            document.removeEventListener('visibilitychange', this._onVisibility);
            window.removeEventListener('pagehide', this._onPageHide);
            window.removeEventListener('pageshow', this._onPageShow);
            this._lifecycleBound = false;
        }
    }

    export default NotificationStream;