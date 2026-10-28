<?php

namespace App\Services\Chatbot;

use App\Models\ChatHistory;

/**
 * Mendeteksi apakah pesan user adalah NIAT AKSI sistem (booking, surat, absensi, riwayat, konfirmasi)
 * sehingga harus langsung dikirim ke AI dan melewati pencarian manual (Lapis 1-6).
 */
class ActionIntent
{
    // Kata kunci niat AKSI. Dicocokkan sebagai awalan kata
    // (misal "pesan" juga cocok dengan "pesankan", "buat" dengan "buatkan").
    private const ACTION_PREFIX_KEYWORDS = [
        'booking', 'book', 'pesan', 'sewa', 'jadwal', 'kosong', 'tersedia',
        'cek', 'surat', 'absensi', 'riwayat', 'buat', 'ajukan',
    ];

    // Kata pendek yang harus cocok persis (kata utuh), supaya "ya" tidak cocok dengan "saya", dll.
    private const ACTION_EXACT_KEYWORDS = ['ya', 'oke', 'setuju'];

    /**
     * [FIX] Deteksi niat aksi sistem (booking, surat, absensi, riwayat, konfirmasi).
     */
    public function isActionIntent(string $lower): bool
    {
        $prefix = implode('|', array_map(
            fn (string $k): string => preg_quote($k, '/'),
            self::ACTION_PREFIX_KEYWORDS
        ));

        $exact = implode('|', array_map(
            fn (string $k): string => preg_quote($k, '/'),
            self::ACTION_EXACT_KEYWORDS
        ));

        return preg_match('/\b(?:'.$prefix.')\w*/iu', $lower) === 1
            || preg_match('/\b(?:'.$exact.')\b/iu', $lower) === 1;
    }

    /**
     * [FIX] Deteksi jawaban LANJUTAN dari alur aksi.
     * Contoh: AI bertanya "nama penanggung jawab, keperluan, jumlah peserta?" lalu user membalas "risa, rapat, 56".
     * Balasan seperti itu tidak mengandung kata kunci aksi, tapi harus tetap dikirim ke AI (mode aksi).
     */
    public function isActionContinuation(string $sessionId): bool
    {
        try {
            $history = ChatHistory::recentForSession($sessionId, 4);
        } catch (\Throwable $e) {
            return false;
        }

        foreach (array_reverse($history) as $item) {
            if (($item['role'] ?? '') !== 'assistant') {
                continue;
            }

            // Hanya pesan assistant TERAKHIR yang dicek
            return preg_match(
                '/penanggung jawab|keperluan|jumlah peserta|melanjutkan booking|lanjut(kan)? (ke )?booking|konfirmasi|nama sesi|jenis surat|isi surat|tanggal berapa|sesi (pagi|siang|seharian)|aula mana|id aula|lanjut(kan)\?/iu',
                (string) ($item['content'] ?? '')
            ) === 1;
        }

        return false;
    }
}
