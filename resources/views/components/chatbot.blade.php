{{-- resources/views/components/chatbot.blade.php --}}
{{-- Chatbot AI — Modern Neutral + Aksen Indigo + Typewriter Effect --}}

@once
<style>
    /* ═══════════════════════════════════════════════════════════ */
    /* PALET WARNA — Netral slate + aksen indigo                     */
    /* ═══════════════════════════════════════════════════════════ */
    #chatbot-bubble,
    #chatbot-toggle {
        --cb-accent:        #6366f1;
        --cb-accent-hover:  #4f46e5;
        --cb-accent-soft:   rgba(99, 102, 241, 0.1);
        --cb-accent-ring:   rgba(99, 102, 241, 0.25);
        --cb-text:          #0f172a;
        --cb-text-muted:    #64748b;
        --cb-bg-chat:       #f8fafc;
        --cb-surface:       #ffffff;
        --cb-surface-hover: #f1f5f9;
        --cb-border:        #e2e8f0;
    }
    .dark #chatbot-bubble,
    .dark #chatbot-toggle {
        --cb-accent:        #818cf8;
        --cb-accent-hover:  #a5b4fc;
        --cb-accent-soft:   rgba(129, 140, 248, 0.12);
        --cb-accent-ring:   rgba(129, 140, 248, 0.3);
        --cb-text:          #f1f5f9;
        --cb-text-muted:    #94a3b8;
        --cb-bg-chat:       #0f172a;
        --cb-surface:       #1e293b;
        --cb-surface-hover: #334155;
        --cb-border:        #334155;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* BUBBLE PESAN                                                  */
    /* ═══════════════════════════════════════════════════════════ */
    .chatbot-bg {
        background: var(--cb-bg-chat);
        position: relative;
    }
    .chatbot-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at 1px 1px, var(--cb-border) 1px, transparent 0);
        background-size: 22px 22px;
        pointer-events: none;
        mask-image: linear-gradient(180deg, transparent 0%, black 15%, black 85%, transparent 100%);
        -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 15%, black 85%, transparent 100%);
        opacity: 0.4;
    }

    .chatbot-row-bot  { display: flex; justify-content: flex-start; align-items: flex-start; gap: 8px; }
    .chatbot-row-user { display: flex; justify-content: flex-end; }

    /* ── Avatar bot ── */
    .chatbot-bot-avatar {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--cb-surface);
        border: 1px solid var(--cb-border);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
        overflow: hidden;
        padding: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .chatbot-bot-avatar img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    /* ── Bubble ── */
    .chatbot-bubble-bot,
    .chatbot-bubble-user {
        position: relative;
        max-width: 82%;
        border-radius: 1rem;
        padding: 0.65rem 0.9rem 0.45rem;
        font-size: 0.875rem;
        line-height: 1.5;
        animation: chatbot-pop 260ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes chatbot-pop {
        from { opacity: 0; transform: translateY(6px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .chatbot-bubble-bot {
        background: var(--cb-surface);
        color: var(--cb-text);
        border: 1px solid var(--cb-border);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        border-bottom-left-radius: 0.25rem;
    }
    .chatbot-bubble-user {
        background: var(--cb-accent);
        color: #ffffff;
        border-bottom-right-radius: 0.25rem;
        box-shadow: 0 2px 8px -2px var(--cb-accent-ring);
    }

    .chatbot-text {
        white-space: pre-wrap;
        word-break: break-word;
        padding-right: 4.5rem;
    }
    .chatbot-meta {
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.25rem;
        font-size: 10px;
        font-weight: 500;
        color: var(--cb-text-muted);
        letter-spacing: 0.02em;
        opacity: 0.75;
    }
    .chatbot-bubble-user .chatbot-meta { color: rgba(255, 255, 255, 0.8); opacity: 1; }
    .chatbot-check {
        width: 13px;
        height: 12px;
        color: #ffffff;
    }

    /* ── Tombol copy ── */
    .chatbot-copy-btn {
        position: absolute;
        top: 0.375rem;
        right: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: transparent;
        border: none;
        color: var(--cb-text-muted);
        cursor: pointer;
        opacity: 0;
        transition: all 0.18s ease;
        z-index: 2;
    }
    .chatbot-bubble-bot:hover .chatbot-copy-btn,
    .chatbot-bubble-user:hover .chatbot-copy-btn { opacity: 1; }
    .chatbot-bubble-bot .chatbot-copy-btn:hover {
        background: var(--cb-surface-hover);
        color: var(--cb-accent);
    }
    .chatbot-bubble-user .chatbot-copy-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }
    .chatbot-copy-btn:active { transform: scale(0.9); }
    .chatbot-copy-btn svg { width: 14px; height: 14px; }
    .chatbot-copy-btn.is-copied {
        color: #10b981;
        background: rgba(16, 185, 129, 0.12);
        opacity: 1;
    }
    @media (max-width: 640px) {
        .chatbot-copy-btn { opacity: 0.5; }
    }

    /* ── Kursor Typewriter ── */
    .chatbot-typewriter-cursor {
        display: inline-block;
        width: 5px;
        height: 14px;
        background-color: var(--cb-accent);
        margin-left: 2px;
        vertical-align: middle;
        animation: chatbot-blink 0.8s infinite;
        border-radius: 1px;
    }
    @keyframes chatbot-blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* MENU CEPAT                                                    */
    /* ═══════════════════════════════════════════════════════════ */
    .chatbot-quick-wrapper {
        max-width: 100% !important;
        padding: 0 !important;
        background: var(--cb-surface);
        border: 1px solid var(--cb-border);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        border-bottom-left-radius: 0.25rem;
    }
    .chatbot-quick-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.5rem 0.85rem;
        border-radius: 9999px;
        background: var(--cb-accent);
        color: #ffffff;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 8px -2px var(--cb-accent-ring);
        transition: all 0.2s ease;
        margin: 0.625rem 0.75rem;
    }
    .chatbot-quick-toggle:hover {
        background: var(--cb-accent-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px var(--cb-accent-ring);
    }
    .chatbot-quick-toggle:active { transform: scale(0.97); }
    .chatbot-quick-toggle .chatbot-chevron {
        width: 14px;
        height: 14px;
        transition: transform 0.25s ease;
    }
    .chatbot-quick-toggle.is-open .chatbot-chevron { transform: rotate(180deg); }

    .chatbot-quick-list {
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.25s ease,
                    padding 0.3s ease;
        padding: 0 0.75rem;
    }
    .chatbot-quick-list.is-open {
        max-height: 600px;
        opacity: 1;
        padding: 0 0.75rem 0.75rem;
    }
    .chatbot-quick-btn {
        font-size: 0.75rem;
        padding: 0.45rem 0.75rem;
        border-radius: 0.5rem;
        background: var(--cb-surface-hover);
        color: var(--cb-text);
        border: 1px solid var(--cb-border);
        cursor: pointer;
        transition: all 0.18s ease;
        font-weight: 500;
        line-height: 1.2;
        text-align: left;
    }
    .chatbot-quick-btn:hover {
        background: var(--cb-accent-soft);
        color: var(--cb-accent);
        border-color: var(--cb-accent);
    }
    .chatbot-quick-btn:active { transform: scale(0.98); }
    .chatbot-quick-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

    /* ═══════════════════════════════════════════════════════════ */
    /* HEADER BUTTONS                                                */
    /* ═══════════════════════════════════════════════════════════ */
    .chatbot-header-btn {
        display: flex;
        height: 36px;
        width: 36px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: var(--cb-text-muted);
        transition: all 0.15s;
        cursor: pointer;
        background: var(--cb-surface-hover);
        border: 1px solid var(--cb-border);
        padding: 0;
    }
    .chatbot-header-btn:hover {
        background: var(--cb-accent-soft);
        color: var(--cb-accent);
        border-color: var(--cb-accent-ring);
    }
    .chatbot-header-btn:active { transform: scale(0.9); }
    .chatbot-header-btn svg { 
        width: 18px;
        height: 18px; 
        stroke-width: 2.5;
    }

    #chatbot-resize-handle {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 20px;
        height: 20px;
        cursor: nwse-resize;
        z-index: 10;
        color: var(--cb-text-muted);
        transition: color 0.15s;
        opacity: 0.5;
    }
    #chatbot-resize-handle:hover { color: var(--cb-accent); opacity: 1; }
    #chatbot-resize-handle::before {
        content: '';
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 10px;
        height: 10px;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* TOMBOL FLOATING (TOGGLE)                                      */
    /* ═══════════════════════════════════════════════════════════ */
    #chatbot-toggle {
        position: fixed !important;
        bottom: 1.5rem !important;
        right: 1.5rem !important;
        z-index: 2147483647 !important;
        background: var(--cb-accent) !important;
        box-shadow: 0 8px 24px -4px var(--cb-accent-ring),
                    0 4px 12px -4px rgba(0, 0, 0, 0.15) !important;
        padding: 0 !important;
        overflow: visible;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    #chatbot-toggle::before,
    #chatbot-toggle::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: var(--cb-accent);
        z-index: -1;
        opacity: 0.4;
        pointer-events: none;
        animation: chatbot-pulse 3s infinite cubic-bezier(0.4, 0, 0.2, 1);
    }
    #chatbot-toggle::after {
        animation-delay: 1.5s;
    }
    @keyframes chatbot-pulse {
        0% { transform: scale(1); opacity: 0.4; }
        100% { transform: scale(1.5); opacity: 0; }
    }

    #chatbot-toggle:hover {
        background: var(--cb-accent-hover) !important;
        box-shadow: 0 12px 28px -4px var(--cb-accent-ring) !important;
        transform: scale(1.1) rotate(5deg);
    }
    
    #chatbot-toggle img {
        width: 62%;
        height: 62%;
        object-fit: contain;
        filter: brightness(0) invert(1);
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.15));
        transition: transform 0.3s ease;
    }
    #chatbot-toggle:hover img {
        transform: scale(1.1);
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* BUBBLE CONTAINER                                              */
    /* ═══════════════════════════════════════════════════════════ */
    #chatbot-bubble {
        position: fixed;
        bottom: 6.5rem;
        right: 1.5rem;
        z-index: 2147483646;
        width: 25rem;
        max-width: calc(100vw - 2rem);
        transform-origin: bottom right;
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
        transition: opacity 360ms ease-out, transform 420ms cubic-bezier(0.16, 1, 0.3, 1), visibility 420ms;
        visibility: visible;
        will-change: opacity, transform;
    }
    #chatbot-bubble.chatbot-hidden {
        opacity: 0;
        transform: translate3d(18px, 18px, 0) scale(0.12);
        pointer-events: none;
        visibility: hidden;
    }
    #chatbot-bubble.chatbot-closing {
        opacity: 0;
        transform: translate3d(18px, 18px, 0) scale(0.12);
        pointer-events: none;
    }
    @media (prefers-reduced-motion: reduce) {
        #chatbot-bubble, .chatbot-bubble-bot, .chatbot-bubble-user { transition: none; animation: none; }
        .chatbot-quick-list { transition: none; }
        #chatbot-toggle::before, #chatbot-toggle::after { animation: none; display: none; }
    }

    #chatbot-container {
        display: flex;
        flex-direction: column;
        height: 600px;
        max-height: calc(100vh - 8rem);
        overflow: hidden;
        border-radius: 1rem;
        box-shadow:
            0 20px 40px -12px rgba(0, 0, 0, 0.15),
            0 8px 16px -8px rgba(0, 0, 0, 0.1);
        background: var(--cb-surface);
        border: 1px solid var(--cb-border);
    }

    #chatbot-bubble.chatbot-fullscreen {
        top: 1rem !important;
        left: 1rem !important;
        right: 1rem !important;
        bottom: 1rem !important;
        width: auto !important;
        max-width: none !important;
        height: auto !important;
    }
    #chatbot-bubble.chatbot-fullscreen #chatbot-container {
        height: 100% !important;
        max-height: none !important;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* HEADER                                                        */
    /* ═══════════════════════════════════════════════════════════ */
    .chatbot-header {
        position: relative;
        background: var(--cb-surface);
        border-bottom: 1px solid var(--cb-border);
    }
    .chatbot-header-content {
        position: relative;
        z-index: 1;
    }
    .chatbot-header-logo {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        width: 42px;
        border-radius: 10px;
        background: var(--cb-accent-soft);
        border: 1px solid var(--cb-accent-ring);
        flex-shrink: 0;
        overflow: hidden;
        padding: 5px;
    }
    .chatbot-header-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .chatbot-online-dot {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #10b981;
        border: 2px solid var(--cb-surface);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
        animation: chatbot-online-pulse 2s ease-in-out infinite;
    }
    @keyframes chatbot-online-pulse {
        0%, 100% { box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3); }
        50%      { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.1); }
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* INPUT AREA & COOLDOWN BAR                                     */
    /* ═══════════════════════════════════════════════════════════ */
    .chatbot-input-wrapper {
        background: var(--cb-surface);
        border-top: 1px solid var(--cb-border);
        padding: 0.75rem;
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
        position: relative; /* Penting buat posisi progress bar */
    }
    .chatbot-input-box {
        flex: 1;
        display: flex;
        align-items: flex-end;
        border-radius: 1rem;
        background: var(--cb-bg-chat);
        border: 1.5px solid var(--cb-border);
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
    }
    .chatbot-input-box:focus-within {
        background: var(--cb-surface);
        border-color: var(--cb-accent);
        box-shadow: 0 0 0 3px var(--cb-accent-soft);
    }
    #chatbot-input {
        flex: 1;
        resize: none;
        background: transparent;
        border: none;
        padding: 0.375rem 0;
        font-size: 0.875rem;
        color: var(--cb-text);
        outline: none;
        line-height: 1.4;
        max-height: 100px;
    }
    #chatbot-input::placeholder { color: var(--cb-text-muted); opacity: 0.7; }

    #chatbot-send {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        width: 42px;
        flex-shrink: 0;
        border-radius: 12px;
        background: var(--cb-accent);
        color: #ffffff;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 8px -2px var(--cb-accent-ring);
        transition: all 0.18s ease;
    }
    #chatbot-send:hover {
        background: var(--cb-accent-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px var(--cb-accent-ring);
    }
    #chatbot-send:active { transform: translateY(0) scale(0.96); }
    #chatbot-send:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    #chatbot-send svg { width: 18px; height: 18px; }

    #chatbot-typing .chatbot-bubble-bot { padding: 0.75rem 1rem; }

    /* UI Cooldown Baru */
    .chatbot-cooldown-container {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: transparent;
        overflow: hidden;
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
        display: none; /* Disembunyiin dulu */
    }
    .chatbot-cooldown-container.active {
        display: block;
    }
    .chatbot-cooldown-bar {
        height: 100%;
        background: #ef4444; /* Warna merah */
        width: 100%;
        transition: width 1s linear;
    }
    .chatbot-cooldown-text {
        position: absolute;
        right: 1rem;
        bottom: 0.5rem;
        font-size: 10px;
        font-weight: 600;
        color: #ef4444;
        display: none;
    }
    .chatbot-cooldown-text.active {
        display: block;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* RESPONSIF HP                                                  */
    /* ═══════════════════════════════════════════════════════════ */
    @media (max-width: 640px) {
        #chatbot-bubble {
            left: 0 !important;
            right: 0 !important;
            top: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            height: 100vh !important;
            height: 100dvh !important;
            z-index: 2147483646 !important;
        }
        #chatbot-container {
            height: 100% !important;
            max-height: none !important;
            border-radius: 0 !important;
            border: none !important;
        }
        #chatbot-drag-handle { cursor: default !important; }
        #chatbot-resize-handle { display: none !important; }
        .chatbot-bubble-bot, .chatbot-bubble-user { max-width: 88%; font-size: 0.8125rem; }
        .chatbot-quick-btn { font-size: 0.7rem; padding: 0.4rem 0.65rem; }

        #chatbot-toggle {
            bottom: 1.25rem !important;
            right: 1.25rem !important;
            z-index: 2147483647 !important;
        }
    }
</style>
@endonce

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TOMBOL FLOATING — pakai LOGO BAPELIT                        --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<button id="chatbot-toggle"
        type="button"
        title="Tanya Asisten AI"
        class="flex h-14 w-14 items-center justify-center rounded-full text-white transition-transform duration-200 active:scale-95">
    <img src="{{ asset('image/logo.png') }}" alt="Pojok Bapelit" class="h-8 w-8 object-contain">
</button>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- BUBBLE CHATBOT                                               --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="chatbot-bubble" class="chatbot-hidden">
    <div id="chatbot-container">

        {{-- Header --}}
        <div id="chatbot-drag-handle"
             class="chatbot-header relative flex cursor-move select-none items-center gap-3 px-4 py-3 pr-24">
            <div class="chatbot-header-content flex items-center gap-3 w-full">
                <div class="chatbot-header-logo">
                    <img src="{{ asset('image/logo.png') }}" alt="Pojok Bapelit">
                    <span class="chatbot-online-dot"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold" style="color: var(--cb-text);">Asisten AI Bapelit</p>
                    <p class="truncate text-[11px] flex items-center gap-1" style="color: var(--cb-text-muted);">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Online — siap membantu
                    </p>
                </div>
            </div>

            <div class="absolute right-2 top-2 flex items-center gap-1 chatbot-header-content">
                <button type="button"
                        id="chatbot-fullscreen-btn"
                        onclick="toggleChatbotFullscreen()"
                        title="Perbesar"
                        aria-label="Perbesar"
                        class="chatbot-header-btn">
                    <svg id="chatbot-icon-expand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <svg id="chatbot-icon-minimize" class="hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 9V5m0 4H5m4 0l-5-5m11 5V5m0 4h4m-4 0l5-5M9 15v4m0-4H5m4 0l-5 5m11-5v4m0-4h4m-4 0l5 5"/>
                    </svg>
                </button>

                <button type="button"
                        onclick="closeChatbot()"
                        title="Tutup chat"
                        aria-label="Tutup chat"
                        class="chatbot-header-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div id="chatbot-messages"
             class="chatbot-bg flex-1 space-y-2.5 overflow-y-auto p-3"></div>

        {{-- Input --}}
        <form id="chatbot-form" class="chatbot-input-wrapper">
            <div class="chatbot-input-box relative">
                <textarea id="chatbot-input"
                          rows="1"
                          placeholder="Tanya apa saja..."
                          maxlength="1000"></textarea>
            </div>
            <button type="submit" id="chatbot-send" aria-label="Kirim pesan">
                <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>

            {{-- Progress Bar Cooldown (UI Baru) --}}
            <div id="chatbot-cooldown-container" class="chatbot-cooldown-container">
                <div id="chatbot-cooldown-bar" class="chatbot-cooldown-bar"></div>
            </div>
            <span id="chatbot-cooldown-text" class="chatbot-cooldown-text"></span>
        </form>

        <div id="chatbot-resize-handle" title="Tarik untuk ubah ukuran"></div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SCRIPT CHATBOT                                               --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@once
<script>
(function () {
    'use strict';
    if (window.__CHATBOT_INIT__) return;
    window.__CHATBOT_INIT__ = true;

    document.addEventListener('DOMContentLoaded', initChatbot);
    if (document.readyState !== 'loading') initChatbot();

    function initChatbot() {
        if (window.__CHATBOT_BOUND__) return;
        window.__CHATBOT_BOUND__ = true;

        const toggle       = document.getElementById('chatbot-toggle');
        const bubble       = document.getElementById('chatbot-bubble');
        const container    = document.getElementById('chatbot-container');
        const handle       = document.getElementById('chatbot-drag-handle');
        const resizeHandle = document.getElementById('chatbot-resize-handle');
        const form         = document.getElementById('chatbot-form');
        const input        = document.getElementById('chatbot-input');
        const sendBtn      = document.getElementById('chatbot-send');
        const messages     = document.getElementById('chatbot-messages');
        const fsBtn        = document.getElementById('chatbot-fullscreen-btn');
        const iconExpand   = document.getElementById('chatbot-icon-expand');
        const iconMinimize = document.getElementById('chatbot-icon-minimize');

        // Elemen UI Cooldown Baru
        const cooldownContainer = document.getElementById('chatbot-cooldown-container');
        const cooldownBar       = document.getElementById('chatbot-cooldown-bar');
        const cooldownText      = document.getElementById('chatbot-cooldown-text');

        if (!toggle || !bubble) return;

        const chatUrl     = '{{ route('chatbot.chat') }}';
        const menusUrl    = '{{ route('chatbot.menus') }}';
        const templateUrl = '{{ route('chatbot.template') }}';
        const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content;
        const LOGO_URL    = '{{ asset('image/logo.png') }}';

        const MIN_W = 320;
        const MIN_H = 400;
        const MAX_W = () => Math.min(900, window.innerWidth - 32);
        const MAX_H = () => window.innerHeight - 32;

        let menusLoaded  = false;
        let isFullscreen = false;
        
        // State Cooldown
        let cooldownUntil = 0;
        let cooldownTimer = null;
        let isProcessing = false;

        const CHAT_STORAGE_KEY = 'pojok_bapelit_chatbot_history';
        const CHAT_HISTORY_TTL = 24 * 60 * 60 * 1000;
        const isMobile   = () => window.matchMedia('(max-width: 640px)').matches;

        const BOT_AVATAR_HTML = `<img src="${LOGO_URL}" alt="">`;

        function nowTime() {
            const d = new Date();
            return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        }

        function saveChatHistory() {
            const history = [...messages.querySelectorAll('[data-chat-message]')].map((element) => ({
                text: element.dataset.chatText || '',
                isUser: element.dataset.chatUser === '1',
                time: element.dataset.chatTime || nowTime(),
            }));

            localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify({ savedAt: Date.now(), history }));
        }

        function restoreChatHistory() {
            try {
                const saved = JSON.parse(localStorage.getItem(CHAT_STORAGE_KEY) || 'null');
                if (!saved || Date.now() - saved.savedAt > CHAT_HISTORY_TTL || !Array.isArray(saved.history)) {
                    localStorage.removeItem(CHAT_STORAGE_KEY);
                    return;
                }

                saved.history.forEach((message) => addMessage(message.text, message.isUser, message.time, false, true));
                messages.scrollTop = messages.scrollHeight;
            } catch (error) {
                localStorage.removeItem(CHAT_STORAGE_KEY);
                console.warn('[Chatbot] Riwayat tidak dapat dipulihkan:', error);
            }
        }

        function clearInlineStyles() {
            bubble.removeAttribute('style');
            container.removeAttribute('style');
        }

        // ═══════════════════════════════════════════════════════
        // BUKA / TUTUP / FULLSCREEN
        // ═══════════════════════════════════════════════════════
        window.openChatbot = function () {
            bubble.classList.remove('chatbot-hidden', 'chatbot-closing');
            toggle.style.display = 'none';
            setTimeout(() => input && input.focus(), 150);
            if (!menusLoaded) {
                loadMenus();
                menusLoaded = true;
            }
            if (isMobile()) document.body.style.overflow = 'hidden';
        };

        window.closeChatbot = function () {
            bubble.classList.add('chatbot-closing');
            bubble.classList.remove('chatbot-fullscreen');
            clearInlineStyles();
            toggle.style.display = 'flex';
            isFullscreen = false;
            iconExpand.classList.remove('hidden');
            iconMinimize.classList.add('hidden');
            fsBtn.title = 'Perbesar';
            document.body.style.overflow = '';
            setTimeout(() => {
                bubble.classList.remove('chatbot-closing');
                bubble.classList.add('chatbot-hidden');
            }, 220);
        };

        window.toggleChatbotFullscreen = function () {
            isFullscreen = !isFullscreen;

            bubble.classList.toggle('chatbot-fullscreen', isFullscreen);
            iconExpand.classList.toggle('hidden', isFullscreen);
            iconMinimize.classList.toggle('hidden', !isFullscreen);
            fsBtn.title = isFullscreen ? 'Kecilkan' : 'Perbesar';

            clearInlineStyles();
            setTimeout(() => { messages.scrollTop = messages.scrollHeight; }, 100);
        };

        toggle.addEventListener('click', window.openChatbot);

        // ═══════════════════════════════════════════════════════
        // DRAG
        // ═══════════════════════════════════════════════════════
        let isDragging = false;
        let dragStartX = 0, dragStartY = 0, dragStartLeft = 0, dragStartTop = 0;

        handle.addEventListener('mousedown', startDrag);
        handle.addEventListener('touchstart', startDrag, { passive: true });

        function startDrag(e) {
            if (isMobile() || isFullscreen) return;
            if (e.target.closest('button')) return;

            isDragging = true;
            const rect = bubble.getBoundingClientRect();
            Object.assign(bubble.style, {
                position: 'fixed',
                top: rect.top + 'px',
                left: rect.left + 'px',
                bottom: 'auto',
                right: 'auto',
                zIndex: '2147483646',
                width: rect.width + 'px',
                maxWidth: 'none',
            });

            const p = e.touches ? e.touches[0] : e;
            dragStartX = p.clientX;
            dragStartY = p.clientY;
            dragStartLeft = rect.left;
            dragStartTop = rect.top;

            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', stopDrag);
            document.addEventListener('touchmove', onDrag, { passive: false });
            document.addEventListener('touchend', stopDrag);
            document.body.style.userSelect = 'none';
        }

        function onDrag(e) {
            if (!isDragging) return;
            e.preventDefault();
            const p = e.touches ? e.touches[0] : e;
            const newLeft = Math.max(8, Math.min(dragStartLeft + p.clientX - dragStartX, window.innerWidth - bubble.offsetWidth - 8));
            const newTop  = Math.max(8, Math.min(dragStartTop + p.clientY - dragStartY, window.innerHeight - bubble.offsetHeight - 8));
            bubble.style.left = newLeft + 'px';
            bubble.style.top = newTop + 'px';
        }

        function stopDrag() {
            isDragging = false;
            document.removeEventListener('mousemove', onDrag);
            document.removeEventListener('mouseup', stopDrag);
            document.removeEventListener('touchmove', onDrag);
            document.removeEventListener('touchend', stopDrag);
            document.body.style.userSelect = '';
        }

        // ═══════════════════════════════════════════════════════
        // RESIZE
        // ═══════════════════════════════════════════════════════
        let isResizing = false;
        let resizeStartX = 0, resizeStartY = 0, resizeStartW = 0, resizeStartH = 0;

        resizeHandle.addEventListener('mousedown', startResize);
        resizeHandle.addEventListener('touchstart', startResize, { passive: true });

        function startResize(e) {
            if (isMobile() || isFullscreen) return;
            e.preventDefault();
            e.stopPropagation();

            isResizing = true;
            const rect = bubble.getBoundingClientRect();

            Object.assign(bubble.style, {
                position: 'fixed',
                top: rect.top + 'px',
                left: rect.left + 'px',
                bottom: 'auto',
                right: 'auto',
                zIndex: '2147483646',
                width: rect.width + 'px',
                maxWidth: 'none',
            });
            container.style.height = rect.height + 'px';

            const p = e.touches ? e.touches[0] : e;
            resizeStartX = p.clientX;
            resizeStartY = p.clientY;
            resizeStartW = rect.width;
            resizeStartH = rect.height;

            document.addEventListener('mousemove', onResize);
            document.addEventListener('mouseup', stopResize);
            document.addEventListener('touchmove', onResize, { passive: false });
            document.addEventListener('touchend', stopResize);
            document.body.style.userSelect = 'none';
        }

        function onResize(e) {
            if (!isResizing) return;
            e.preventDefault();
            const p = e.touches ? e.touches[0] : e;
            const newW = Math.max(MIN_W, Math.min(resizeStartW + p.clientX - resizeStartX, MAX_W()));
            const newH = Math.max(MIN_H, Math.min(resizeStartH + p.clientY - resizeStartY, MAX_H()));
            bubble.style.width = newW + 'px';
            container.style.height = newH + 'px';
        }

        function stopResize() {
            isResizing = false;
            document.removeEventListener('mousemove', onResize);
            document.removeEventListener('mouseup', stopResize);
            document.removeEventListener('touchmove', onResize);
            document.removeEventListener('touchend', stopResize);
            document.body.style.userSelect = '';
        }

        // ═══════════════════════════════════════════════════════
        // FUNGSI COUNTDOWN VISUAL (PROGRESS BAR)
        // ═══════════════════════════════════════════════════════
        function startVisualCooldown(seconds) {
            cooldownUntil = Date.now() + (seconds * 1000);
            
            if (cooldownTimer) clearInterval(cooldownTimer);
            
            cooldownContainer.classList.add('active');
            cooldownText.classList.add('active');
            
            // Set full width di awal
            cooldownBar.style.transition = 'none';
            cooldownBar.style.width = '100%';
            cooldownText.textContent = `${seconds} detik`;

            // Kasih delay dikit biar transisi width-nya jalan
            setTimeout(() => {
                cooldownBar.style.transition = `width ${seconds}s linear`;
                cooldownBar.style.width = '0%';
            }, 50);

            // Update teks tiap detik
            cooldownTimer = setInterval(() => {
                const sisa = Math.ceil((cooldownUntil - Date.now()) / 1000);
                
                if (sisa > 0) {
                    cooldownText.textContent = `${sisa} detik`;
                } else {
                    // Selesai
                    clearInterval(cooldownTimer);
                    cooldownContainer.classList.remove('active');
                    cooldownText.classList.remove('active');
                    cooldownUntil = 0;
                    input.placeholder = 'Tanya apa saja...';
                }
            }, 1000);
        }

        // ═══════════════════════════════════════════════════════
        // INPUT
        // ═══════════════════════════════════════════════════════
        input.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!input.disabled) form.requestSubmit();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !bubble.classList.contains('chatbot-hidden')) {
                if (isFullscreen) {
                    window.toggleChatbotFullscreen();
                } else {
                    window.closeChatbot();
                }
            }
        });

        // ═══════════════════════════════════════════════════════
        // HELPER: COPY KE CLIPBOARD
        // ═══════════════════════════════════════════════════════
        async function copyToClipboard(text, btnEl) {
            const originalHTML = btnEl.innerHTML;
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.focus();
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
                btnEl.classList.add('is-copied');
                btnEl.innerHTML = `
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>`;
                setTimeout(() => {
                    btnEl.classList.remove('is-copied');
                    btnEl.innerHTML = originalHTML;
                }, 1500);
            } catch (err) {
                console.warn('[Chatbot] Gagal copy:', err);
                btnEl.innerHTML = `
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>`;
                setTimeout(() => { btnEl.innerHTML = originalHTML; }, 1500);
            }
        }

        // ═══════════════════════════════════════════════════════
        // EFFECT: TYPEWRITER
        // ═══════════════════════════════════════════════════════
        function typeWriter(element, text, speed = 15, callback) {
            let i = 0;
            element.textContent = '';
            
            const cursor = document.createElement('span');
            cursor.className = 'chatbot-typewriter-cursor';
            element.appendChild(cursor);

            function type() {
                if (i < text.length) {
                    cursor.insertAdjacentText('beforebegin', text.charAt(i));
                    i++;
                    messages.scrollTop = messages.scrollHeight;
                    setTimeout(type, speed);
                } else {
                    cursor.remove();
                    if (callback) callback();
                }
            }
            type();
        }

        // ═══════════════════════════════════════════════════════
        // RENDER BUBBLE PESAN
        // ═══════════════════════════════════════════════════════
        function addMessage(text, isUser, time = nowTime(), persist = true, isInstant = false, callback = null) {
            const row = document.createElement('div');
            row.className = isUser ? 'chatbot-row-user' : 'chatbot-row-bot';
            row.dataset.chatMessage = '1';
            row.dataset.chatText = text;
            row.dataset.chatUser = isUser ? '1' : '0';
            row.dataset.chatTime = time;

            if (!isUser) {
                const avatar = document.createElement('div');
                avatar.className = 'chatbot-bot-avatar';
                avatar.innerHTML = BOT_AVATAR_HTML;
                row.appendChild(avatar);
            }

            const b = document.createElement('div');
            b.className = isUser ? 'chatbot-bubble-user' : 'chatbot-bubble-bot';

            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'chatbot-copy-btn';
            copyBtn.title = 'Copy teks';
            copyBtn.setAttribute('aria-label', 'Copy teks');
            copyBtn.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>`;
            copyBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                copyToClipboard(text, copyBtn);
            });
            b.appendChild(copyBtn);

            const textEl = document.createElement('div');
            textEl.className = 'chatbot-text';
            
            if (!isUser && !isInstant) {
                b.appendChild(textEl);
                typeWriter(textEl, text, 15, callback);
            } else {
                textEl.textContent = text;
                b.appendChild(textEl);
                if (callback) callback();
            }

            const meta = document.createElement('div');
            meta.className = 'chatbot-meta';
            meta.innerHTML = `<span>${time}</span>`;
            if (isUser) {
                meta.innerHTML += `<svg class="chatbot-check" viewBox="0 0 16 15" fill="currentColor"><path d="M15.01 3.316l-.478-.372a.365.365 0 00-.51.063L8.666 9.879a.32.32 0 01-.484.033l-.358-.325a.319.319 0 00-.484.032l-.378.483a.418.418 0 00.036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 00-.063-.51zm-4.1 0l-.478-.372a.365.365 0 00-.51.063L4.566 9.879a.32.32 0 01-.484.033l-.423.433a.364.364 0 00.006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 00-.063-.51z"/></svg>`;
            }
            b.appendChild(meta);

            row.appendChild(b);
            messages.appendChild(row);
            messages.scrollTop = messages.scrollHeight;
            if (persist) saveChatHistory();
        }

        restoreChatHistory();

        function addTyping() {
            const row = document.createElement('div');
            row.className = 'chatbot-row-bot';
            row.id = 'chatbot-typing';

            const avatar = document.createElement('div');
            avatar.className = 'chatbot-bot-avatar';
            avatar.innerHTML = BOT_AVATAR_HTML;
            row.appendChild(avatar);

            const b = document.createElement('div');
            b.className = 'chatbot-bubble-bot';
            b.innerHTML = `
                <div class="flex items-center gap-1 py-1">
                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style="animation-delay:0ms"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style="animation-delay:150ms"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style="animation-delay:300ms"></span>
                </div>`;
            row.appendChild(b);
            messages.appendChild(row);
            messages.scrollTop = messages.scrollHeight;
        }

        function removeTyping() {
            document.getElementById('chatbot-typing')?.remove();
        }

        // ═══════════════════════════════════════════════════════
        // MENU CEPAT (COLLAPSIBLE — "Permintaan Populer")
        // ═══════════════════════════════════════════════════════
        async function loadMenus() {
            try {
                const res = await fetch(menusUrl, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.menus && data.menus.length) renderQuickMenu(data.menus);
            } catch (err) {
                console.error('[Chatbot] Gagal load menu:', err);
            }
        }

        function renderQuickMenu(menus) {
            if (document.getElementById('chatbot-quick-menu')) return;

            const row = document.createElement('div');
            row.className = 'chatbot-row-bot';
            row.id = 'chatbot-quick-menu';

            const avatar = document.createElement('div');
            avatar.className = 'chatbot-bot-avatar';
            avatar.innerHTML = BOT_AVATAR_HTML;
            row.appendChild(avatar);

            const wrapper = document.createElement('div');
            wrapper.className = 'chatbot-bubble-bot chatbot-quick-wrapper';

            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'chatbot-quick-toggle';
            toggleBtn.innerHTML = `
                <svg class="chatbot-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
                <span>✨ Permintaan Populer</span>
            `;

            const menuBox = document.createElement('div');
            menuBox.className = 'chatbot-quick-list';
            menuBox.style.display = 'flex';
            menuBox.style.flexDirection = 'column';
            menuBox.style.gap = '0.375rem';

            menus.forEach(m => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'chatbot-quick-btn';
                btn.textContent = m.label;
                btn.dataset.id = m.id;
                btn.addEventListener('click', () => handleQuickMenu(m.id, btn));
                menuBox.appendChild(btn);
            });

            toggleBtn.addEventListener('click', () => {
                const isOpen = menuBox.classList.toggle('is-open');
                toggleBtn.classList.toggle('is-open', isOpen);
                setTimeout(() => { messages.scrollTop = messages.scrollHeight; }, 320);
            });

            wrapper.appendChild(toggleBtn);
            wrapper.appendChild(menuBox);
            row.appendChild(wrapper);
            messages.appendChild(row);
            messages.scrollTop = messages.scrollHeight;
        }

        function showMenusAgain(delay = 300) {
            setTimeout(() => {
                if (!document.getElementById('chatbot-quick-menu')) {
                    loadMenus();
                }
            }, delay);
        }

        async function handleQuickMenu(id, btnEl) {
            document.querySelectorAll('.chatbot-quick-btn').forEach(b => b.disabled = true);
            addMessage(btnEl.textContent.trim(), true);

            document.getElementById('chatbot-quick-menu')?.remove();
            addTyping();

            try {
                const res = await fetch(templateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ id }),
                });
                const data = await res.json();
                removeTyping();
                addMessage(res.ok ? (data.reply || 'Maaf, saya tidak mengerti.') : ('⚠️ ' + (data.error || 'Gagal memuat jawaban')), false);
            } catch (err) {
                removeTyping();
                addMessage('⚠️ Koneksi gagal. Coba lagi.', false);
                console.error('[Chatbot]', err);
            } finally {
                showMenusAgain(300);
            }
        }

        // ═══════════════════════════════════════════════════════
        // KIRIM PESAN MANUAL
        // ═══════════════════════════════════════════════════════
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const message = input.value.trim();
            
            // Cegah submit kosong atau kalau sedang proses
            if (!message || isProcessing) return;

            // Cek apakah masih dalam masa cooldown
            if (Date.now() < cooldownUntil) {
                const sisa = Math.ceil((cooldownUntil - Date.now()) / 1000);
                addMessage(`Sabar ya, AI-nya masih istirahat. Tunggu ${sisa} detik lagi sebelum kirim.`, false);
                return; 
            }

            document.getElementById('chatbot-quick-menu')?.remove();

            addMessage(message, true);
            input.value = '';
            input.style.height = 'auto';
            
            // KUNCI INPUT SELAMA PROSES API
            isProcessing = true;
            sendBtn.disabled = true;
            input.disabled = true; 
            addTyping();

            try {
                const res = await fetch(chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ message }),
                });
                const data = await res.json();
                removeTyping();

                if (data.source === 'rate_limited' || data.source === 'cooldown' || res.status === 429) {
                    
                    // [FIX] Ambil angka dari teks backend (misal "Coba lagi dalam 9 detik ya")
                    let sisaDetik = 15; 
                    const match = (data.reply || '').match(/(\d+)\s*detik/i);
                    if (match && match[1]) {
                        sisaDetik = parseInt(match[1], 10);
                    }

                    // Panggil progress bar visual dengan sisaDetik
                    startVisualCooldown(sisaDetik);

                    addMessage(data.reply || data.error || 'AI sedang kena batas. Tunggu bentar ya.', false, nowTime(), true, false, () => {
                        isProcessing = false;
                        input.disabled = false;
                        input.placeholder = 'Bisa ngetik, tapi tunggu cooldown buat kirim...';
                        sendBtn.disabled = false; 
                        input.focus();
                    });
                    
                } else {
                    const reply = res.ok ? (data.reply || 'Maaf, saya tidak mengerti.') : ('⚠️ ' + (data.error || 'Gagal menghubungi AI'));
                    
                    addMessage(reply, false, nowTime(), true, false, () => {
                        isProcessing = false;
                        input.disabled = false;
                        input.placeholder = 'Tanya apa saja...';
                        sendBtn.disabled = false;
                        input.focus();
                    });
                }
            } catch (err) {
                removeTyping();
                addMessage('⚠️ Koneksi gagal. Coba lagi.', false, nowTime(), true, false, () => {
                    isProcessing = false;
                    input.disabled = false;
                    sendBtn.disabled = false;
                    input.focus();
                });
                console.error('[Chatbot]', err);
            } finally {
                showMenusAgain(400);
            }
        });
    }
})();
</script>
@endonce