<?php

namespace App\Services\Chatbot;

use App\Models\ChatHistory;

/**
 * Mendeteksi apakah pesan user adalah NIAT AKSI sistem (booking, surat, absensi, riwayat, konfirmasi)
 * sehingga harus langsung dikirim ke AI dan melewati pencarian manual (Lapis 1-6).
 */
class ActionIntent
{
    // 1. KATA KERJA / AKSI (Bisa diikuti imbuhan. Contoh: "booking" cocok dgn "bookingan", "pesan" cocok dgn "pesankan")
    private const ACTION_PREFIX_KEYWORDS = [
        // Booking Aula
        'booking', 'book', 'boking', 'buking', 'bokin', 'pesan', 'psen', 'pesen', 'sewa', 'sew', 
        'jadwal', 'jdwal', 'jadual', 'kosong', 'ksong', 'tersedia', 'tersed', 'avail', 'ready',
        
        // Absensi / Presensi
        'absensi', 'absen', 'absn', 'presensi', 'presen', 'presns', 'kehadiran', 'hadir',
        
        // Surat
        'surat', 'srt', 'surta', 'ajukan', 'ajukn', 'mohon', 'mhn', 'ambil', 'ambl',
        
        // Riwayat
        'riwayat', 'riwat', 'riwyat', 'history', 'histori', 'riwayt', 'lihat', 'liht', 'cek', 'chek',
        
        // Umum / Perintah
        // CATATAN: 'baru' dan 'lagi' SENGAJA DIHAPUS agar tidak memicu aksi saat user cuma bertanya "ada sesi baru?"
        'buat', 'buatkan', 'bikin', 'bikinin', 'tambah', 'tmbh',
        'tolong', 'tlng', 'bantu', 'bntu', 'minta', 'mnta', 'lanjut', 'lnjut'
    ];

    // 2. KATA SINGKATAN / KONFIRMASI (Harus kata utuh, agar "ok" tidak cocok dgn "oktober", "ya" tdk cocok dgn "yang")
    private const ACTION_EXACT_KEYWORDS = [
        // Konfirmasi
        'ya', 'iya', 'yup', 'ok', 'oke', 'okey', 'sip', 'siap', 'setuju', 'stuju', 
        // Singkatan umum
        'pj', 'tgl', 'jam', 'wkt', 'org', 'kpn'
    ];

    /**
     * Deteksi niat aksi sistem (booking, surat, absensi, riwayat, konfirmasi).
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

        // Cek PREFIX (kata kerja/aksi) ATAU Cek EXACT (kata singkatan/konfirmasi utuh)
        return preg_match('/\b(?:'.$prefix.')\w*/iu', $lower) === 1
            || preg_match('/\b(?:'.$exact.')\b/iu', $lower) === 1;
    }

    /**
     * Deteksi jawaban LANJUTAN dari alur aksi.
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

            // Regex pengecekan pertanyaan AI (termasuk typo/singkatan)
            return preg_match(
                '/penanggung jawab|pj|nama|keperluan|acara|kegiatan|agenda|jumlah peserta|peserta|berapa orang|kapasitas|' .
                'melanjutkan booking|lanjut(kan)? (ke )?booking|konfirmasi|setuju|' .
                'nama sesi|sesi absensi|presensi|lokasi|tempat|ruangan|' .
                'jenis surat|sifat surat|isi surat|tujuan surat|alamat|' .
                'tanggal berapa|tanggal|tgl|jam|waktu|wkt|' .
                'sesi (pagi|siang|seharian)|pagi|siang|seharian|' .
                'aula mana|id aula|pilih aula|pilih bidang|pilih jabatan|' .
                'lanjut(kan)\?|yakin|apakah anda/i',
                (string) ($item['content'] ?? '')
            ) === 1;
        }

        return false;
    }
}