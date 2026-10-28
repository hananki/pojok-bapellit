<?php

namespace App\Services\Chatbot\Answers;

use App\Models\Aula;

/**
 * LAPIS 1: Pencarian umum di database (Aula).
 */
class AulaAnswer
{
    /**
     * Pencarian umum ke tabel non-spesifik (Aula dll).
     */
    public function answer(string $lower): ?string
    {
        if (strlen($lower) < 5 || preg_match('/\b(halo|hi|hai|terima kasih|ok|oke|iya|tidak)\b/iu', $lower)) {
            return null;
        }

        // ── AULA ─────────────────────────────────────────
        if (preg_match('/\b(aula|ruangan|ruang|gedung)\b/iu', $lower)) {
            $isGeneral = (bool) preg_match('/\b(info|daftar|list|semua|apa saja|ada apa|berapa|jumlah|total)\b/iu', $lower);

            if ($isGeneral) {
                $aulas = Aula::all(['nama', 'lokasi']);

                if ($aulas->isEmpty()) {
                    return 'Belum ada data aula di database.';
                }

                $lines = $aulas->map(function ($a, $i) {
                    $nama = trim((string) $a->nama);
                    $lokasi = trim(preg_replace('/[📍📌]/u', '', (string) $a->lokasi));

                    return ($i + 1).'. '.$nama.($lokasi !== '' ? ' — Lokasi: '.$lokasi : ' — Lokasi belum diisi');
                });

                return "Ada {$aulas->count()} aula terdaftar:\n".$lines->implode("\n");
            }

            // Pencarian spesifik
            $searchTerm = trim(preg_replace(
                '/\b(info|daftar|list|aula|ruangan|ruang|lokasi|gedung|apa|saja|ada|berapa|dimana|di mana|yang)\b/iu',
                '',
                $lower
            ));

            if ($searchTerm !== '' && mb_strlen($searchTerm) >= 3) {
                $aulas = Aula::where('nama', 'LIKE', "%$searchTerm%")
                    ->orWhere('lokasi', 'LIKE', "%$searchTerm%")
                    ->get();

                if ($aulas->isNotEmpty()) {
                    $lines = $aulas->map(function ($a, $i) {
                        $nama = trim((string) $a->nama);
                        $lokasi = trim(preg_replace('/[📍📌]/u', '', (string) $a->lokasi));

                        return ($i + 1).'. '.$nama.($lokasi !== '' ? ' — Lokasi: '.$lokasi : ' — Lokasi belum diisi');
                    });

                    return "Ditemukan {$aulas->count()} aula:\n".$lines->implode("\n");
                }
            }

            // Pertanyaan tentang aula tapi tidak ada match — STOP
            return 'Belum ada data aula yang cocok. Coba sebutkan nama atau lokasi aula lebih spesifik.';
        }

        return null;
    }
}
