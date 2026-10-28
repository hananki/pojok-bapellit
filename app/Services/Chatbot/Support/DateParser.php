<?php

namespace App\Services\Chatbot\Support;

/**
 * Parser tanggal untuk chatbot: nama bulan Indonesia/Inggris, kata relatif (besok, lusa),
 * serta validasi tanggal yang sudah lewat.
 */
class DateParser
{
    private const MONTHS = [
        'januari' => 1, 'january' => 1, 'jan' => 1,
        'februari' => 2, 'february' => 2, 'febuari' => 2, 'feb' => 2,
        'maret' => 3, 'march' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'mei' => 5, 'may' => 5,
        'juni' => 6, 'june' => 6, 'jun' => 6,
        'juli' => 7, 'july' => 7, 'jul' => 7,
        'agustus' => 8, 'august' => 8, 'agu' => 8, 'agt' => 8, 'aug' => 8,
        'september' => 9, 'sept' => 9, 'sep' => 9,
        'oktober' => 10, 'october' => 10, 'okt' => 10, 'oct' => 10,
        'november' => 11, 'nopember' => 11, 'nov' => 11,
        'desember' => 12, 'december' => 12, 'des' => 12, 'dec' => 12,
    ];

    /**
     * Ubah teks tanggal apa pun (YYYY-MM-DD, "17 Juli", "July 17, 2026", "17/7", "besok", dst.) menjadi Carbon.
     * Tahun tidak disebut = tahun berjalan. Return null jika tidak dikenali.
     */
    public function parse(string $raw): ?\Illuminate\Support\Carbon
    {
        $tz = 'Asia/Jakarta';
        $text = mb_strtolower(trim($raw));
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        $text = preg_replace('/^(tanggal|tgl|pada|on)\s+/u', '', $text);

        if ($text === '') {
            return null;
        }

        $today = now($tz)->startOfDay();

        $relative = ['hari ini' => 0, 'today' => 0, 'besok' => 1, 'tomorrow' => 1, 'lusa' => 2];
        if (isset($relative[$text])) {
            return $today->copy()->addDays($relative[$text]);
        }

        $make = static function (int $y, int $m, int $d) use ($tz): ?\Illuminate\Support\Carbon {
            if ($y < 1900 || $y > 2200 || ! checkdate($m, $d, $y)) {
                return null;
            }

            return \Illuminate\Support\Carbon::create($y, $m, $d, 0, 0, 0, $tz);
        };

        $year = (int) $today->year;

        // 2026-07-17
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $text, $m)) {
            return $make((int) $m[1], (int) $m[2], (int) $m[3]);
        }

        // 17/07/2026, 17-07-2026, 17.07.2026
        if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/', $text, $m)) {
            return $make((int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // 17/7 (tanpa tahun)
        if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})$/', $text, $m)) {
            return $make($year, (int) $m[2], (int) $m[1]);
        }

        $names = array_keys(self::MONTHS);
        usort($names, fn ($a, $b) => strlen($b) <=> strlen($a));
        $names = implode('|', array_map(fn ($n) => preg_quote($n, '/'), $names));

        // 17 juli 2026 | 17th of july | 17 jul
        if (preg_match('/^(\d{1,2})(?:st|nd|rd|th)?\s+(?:of\s+)?('.$names.')\.?,?(?:\s+(\d{4}))?$/u', $text, $m)) {
            return $make(! empty($m[3]) ? (int) $m[3] : $year, self::MONTHS[$m[2]], (int) $m[1]);
        }

        // july 17 | july 17th, 2026
        if (preg_match('/^('.$names.')\.?\s+(\d{1,2})(?:st|nd|rd|th)?,?(?:\s+(\d{4}))?$/u', $text, $m)) {
            return $make(! empty($m[3]) ? (int) $m[3] : $year, self::MONTHS[$m[1]], (int) $m[2]);
        }

        return null;
    }

    public function formatIndo(\Illuminate\Support\Carbon $date): string
    {
        return $date->copy()->locale('id')->translatedFormat('l, j F Y');
    }

    /**
     * Normalisasi + validasi tanggal booking. Mengubah $arguments[$key] menjadi YYYY-MM-DD.
     * Return pesan error (string) jika tanggal kosong / tidak dikenali / sudah lewat, atau null jika aman.
     */
    public function resolveBookingDate(array &$arguments, string $key = 'tanggal_booking'): ?string
    {
        $raw = trim((string) ($arguments[$key] ?? ''));

        if ($raw === '') {
            return 'Tanggal belum diisi. Tanyakan ke user tanggal berapa yang diinginkan.';
        }

        $date = $this->parse($raw);

        if ($date === null) {
            return "Tanggal \"{$raw}\" tidak dikenali. Minta user menyebutkan tanggalnya dengan jelas, misalnya 17 Juli atau 17 July.";
        }

        $arguments[$key] = $date->format('Y-m-d');

        $today = now('Asia/Jakarta')->startOfDay();
        if ($date->lt($today)) {
            return 'Tanggal '.$this->formatIndo($date).' sudah lewat (hari ini '.$this->formatIndo($today).'), '
                .'jadi tidak bisa dicek atau dibooking. Silakan pilih tanggal hari ini atau setelahnya.';
        }

        return null;
    }

    /**
     * Normalisasi tanggal opsional (absensi/surat) tanpa memblokir tanggal lampau.
     */
    public function normalizeOptionalDate(array $arguments, string $key): array
    {
        if (! empty($arguments[$key])) {
            $date = $this->parse((string) $arguments[$key]);
            if ($date !== null) {
                $arguments[$key] = $date->format('Y-m-d');
            }
        }

        return $arguments;
    }
}
