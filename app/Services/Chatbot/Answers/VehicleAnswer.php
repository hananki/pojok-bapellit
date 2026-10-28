<?php

namespace App\Services\Chatbot\Answers;

use App\Models\Kendaraan;
use Illuminate\Support\Collection;

/**
 * LAPIS 2 & 3: Data kendaraan (spesifik: plat/merk/pemakai) dan ringkasan (jumlah, pajak jatuh tempo).
 */
class VehicleAnswer
{
    /**
     * Menjawab pertanyaan kendaraan SPESIFIK (plat / merk / nama pemakai).
     * Kalau cuma pertanyaan umum, return null biar diteruskan ke summary.
     */
    public function specific(string $message, string $lower): ?string
    {
        $generalTopicWords = [
            'kendaraan', 'mobil', 'motor', 'plat', 'pajak', 'pemakai', 'dipakai', 'pakai',
            'jatuh', 'tempo', 'merk', 'habis', 'abis', 'berakhir', 'kadaluarsa', 'expired',
            'pkb', 'stnk', 'samsat', 'cek', 'info', 'data', 'daftar', 'list', 'semua',
            'apa', 'yang', 'siapa', 'berapa', 'jumlah', 'total', 'segera', 'dekat', 'mau',
            'mana', 'dimana', 'kapan', 'tolong', 'lihat', 'cari', 'dong', 'nih', 'ya',
            'untuk', 'ini', 'itu', 'dengan', 'dan', 'atau',
        ];

        $queryTokens = collect(preg_split('/[^\p{L}\p{N}]+/u', $lower, -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn (string $token): bool => mb_strlen($token) >= 3)
            ->values();

        $meaningfulTokens = $queryTokens
            ->reject(fn (string $token): bool => in_array($token, $generalTopicWords, true))
            ->values();

        if ($meaningfulTokens->isEmpty()) {
            return null;
        }

        $hasNumericToken = $meaningfulTokens->contains(fn (string $t): bool => (bool) preg_match('/\d/', $t));
        $hasStrongKeyword = $meaningfulTokens->contains(fn (string $t): bool => mb_strlen($t) >= 4);

        if (! $hasNumericToken && ! $hasStrongKeyword) {
            return null;
        }

        $normalize = static fn (string $value): string => preg_replace('/[^\p{L}\p{N}]/u', '', mb_strtolower($value)) ?? '';
        $normalizedQueryTokens = $meaningfulTokens->map($normalize)->filter()->values();

        $vehicles = Kendaraan::with(['pajakTahunanAktif', 'pajakLimaTahunanAktif'])->get();

        $matches = $vehicles->filter(function (Kendaraan $item) use ($normalizedQueryTokens, $normalize): bool {
            if ($normalizedQueryTokens->isEmpty()) {
                return false;
            }

            $plat = $normalize((string) $item->plat_nomor);
            $pemakai = $normalize((string) $item->pemakai);
            $merk = $normalize((string) $item->merk);

            foreach ($normalizedQueryTokens as $token) {
                if ($plat !== '' && preg_match('/\b'.preg_quote($token, '/').'\b/u', $plat)) {
                    return true;
                }

                if ($merk !== '' && mb_strlen($token) >= 3 && preg_match('/\b'.preg_quote($token, '/').'\b/u', $merk)) {
                    return true;
                }

                if ($pemakai !== '' && mb_strlen($token) >= 4 && preg_match('/\b'.preg_quote($token, '/').'\b/u', $pemakai)) {
                    return true;
                }
            }

            return false;
        })->values();

        if ($matches->isEmpty()) {
            return null;
        }

        $answers = $matches->map(function (Kendaraan $vehicle): string {
            $answer = "Plat {$vehicle->plat_nomor}: dipakai oleh ".($vehicle->pemakai ?: 'belum diisi');
            if ($vehicle->merk) {
                $answer .= ", merk {$vehicle->merk}";
            }
            if ($vehicle->unit_kerja) {
                $answer .= ", unit {$vehicle->unit_kerja}";
            }
            $answer .= '.';

            $taxes = collect([
                ['label' => 'Pajak tahunan',   'tax' => $vehicle->pajakTahunanAktif],
                ['label' => 'Pajak 5 tahunan', 'tax' => $vehicle->pajakLimaTahunanAktif],
            ])->filter(fn (array $item): bool => $item['tax'] !== null);

            if ($taxes->isEmpty()) {
                return $answer.' Belum ada data pajak aktif.';
            }

            foreach ($taxes as $item) {
                $tax = $item['tax'];
                $days = $tax->sisa_hari;
                $remaining = $days < 0
                    ? 'terlambat '.abs($days).' hari'
                    : ($days === 0 ? 'jatuh tempo hari ini' : "tersisa {$days} hari");
                $answer .= " {$item['label']} sampai {$tax->tanggal_jatuh_tempo->format('d-m-Y')} ({$remaining}).";
            }

            return $answer;
        });

        return $matches->count() === 1
            ? $answers->first()
            : "Ditemukan {$matches->count()} kendaraan:\n- ".$answers->implode("\n- ");
    }

    /**
     * Ringkasan kendaraan: ambil data kendaraan lalu jawab jumlah / pajak jatuh tempo.
     */
    public function summary(string $lower): ?string
    {
        return $this->answerVehicleSummaryQuestion(
            $lower,
            Kendaraan::with(['pajakTahunanAktif', 'pajakLimaTahunanAktif'])->get()
        );
    }

    /**
     * Ringkasan kendaraan (jumlah, pajak terdekat).
     */
    private function answerVehicleSummaryQuestion(string $lower, Collection $vehicles): ?string
    {
        $asksCount = preg_match('/\b(berapa|jumlah|ada berapa|total)\b/iu', $lower) === 1;

        if ($asksCount && preg_match('/\b(kendaraan|motor|mobil|unit)\b/iu', $lower) === 1) {
            $motorCount = $vehicles->filter(fn (Kendaraan $v): bool => preg_match('/\b(motor|zx|vario|beat|scoopy|nmax|matic)\b/iu', (string) $v->merk) === 1)->count();
            $carCount = $vehicles->filter(fn (Kendaraan $v): bool => preg_match('/\b(mobil|toyota|daihatsu|gran|max|avanza|inova|suv)\b/iu', (string) $v->merk) === 1)->count();

            return "Saat ini ada {$vehicles->count()} kendaraan terdaftar"
                .($motorCount || $carCount ? ": {$motorCount} motor dan {$carCount} mobil." : '.');
        }

        $mentionsPajak = preg_match('/\b(pajak|pkb|stnk|samsat)\b/iu', $lower) === 1;

        if ($mentionsPajak) {
            $limitDays = 30;
            if (preg_match('/\b(60|dua bulan|2 bulan)\b/iu', $lower) === 1) {
                $limitDays = 60;
            } elseif (preg_match('/\b(7|tujuh|seminggu|1 minggu|satu minggu)\b/iu', $lower) === 1) {
                $limitDays = 7;
            } elseif (preg_match('/\b(14|dua minggu|2 minggu)\b/iu', $lower) === 1) {
                $limitDays = 14;
            } elseif (preg_match('/\b(90|tiga bulan|3 bulan)\b/iu', $lower) === 1) {
                $limitDays = 90;
            }

            $taxes = $vehicles->flatMap(fn (Kendaraan $v) => collect([
                ['vehicle' => $v, 'type' => 'Pajak tahunan',   'tax' => $v->pajakTahunanAktif],
                ['vehicle' => $v, 'type' => 'Pajak 5 tahunan', 'tax' => $v->pajakLimaTahunanAktif],
            ]))
                ->filter(fn (array $item): bool => $item['tax'] !== null
                    && $item['tax']->sisa_hari >= 0
                    && $item['tax']->sisa_hari <= $limitDays
                )
                ->sortBy(fn (array $item): int => $item['tax']->sisa_hari);

            if ($taxes->isEmpty()) {
                return "Belum ada pajak kendaraan yang jatuh tempo dalam {$limitDays} hari ke depan. Semua pajak masih aman.";
            }

            $lines = $taxes->map(function (array $item, int $i): string {
                $tax = $item['tax'];
                $sisa = $tax->sisa_hari;

                if ($sisa === 0) {
                    $status = 'JATUH TEMPO HARI INI';
                } elseif ($sisa <= 7) {
                    $status = "{$sisa} hari lagi (URGENT)";
                } else {
                    $status = "{$sisa} hari lagi";
                }

                $pemakai = $item['vehicle']->pemakai ?: 'belum diisi';

                return ($i + 1).". {$item['vehicle']->plat_nomor} ({$pemakai})\n"
                     ."   {$item['type']} — jatuh tempo {$tax->tanggal_jatuh_tempo->format('d-m-Y')} ({$status})";
            });

            return "Ada {$taxes->count()} pajak yang jatuh tempo dalam {$limitDays} hari ke depan:\n"
                .$lines->implode("\n");
        }

        return null;
    }
}
