<?php

namespace App\Services\Chatbot\Tools;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Kelompok tool (function calling) per domain: booking, absensi, surat, dst.
 * Untuk menambah fitur baru: buat class baru yang meng-implement interface ini,
 * lalu daftarkan di ToolRegistry.
 */
interface ToolGroup
{
    /** Definisi tool (format OpenAI-compatible) untuk dikirim ke Groq. */
    public function definitions(): array;

    /** Apakah group ini menangani tool dengan nama tersebut? */
    public function handles(string $name): bool;

    /**
     * Jalankan tool. $arguments boleh dinormalisasi (mis. tanggal) dan dipakai ulang oleh describe().
     */
    public function run(string $name, array &$arguments, Authenticatable $user): string;

    /** Kalimat hasil yang siap tampil ke user (dipakai sebagai fallback jika AI gagal menyusun balasan). */
    public function describe(string $name, array $arguments, string $result): string;
}
