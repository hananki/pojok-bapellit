<?php

namespace App\Services\Chatbot\Tools;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Daftar semua tool AI. Menggabungkan definisi tool dan menjalankan tool yang dipanggil AI.
 */
class ToolRegistry
{
    /** @var ToolGroup[] */
    private array $groups;

    public function __construct(
        BookingTools $booking,
        AbsensiTools $absensi,
        SuratTools $surat,
        AbsensiHistoryTools $absensiHistory,
        UserManagementTools $userManagement,
    ) {
        $this->groups = [
            $booking,
            $absensi,
            $surat,
            $absensiHistory,
            $userManagement,
        ];
    }

    public function definitions(): array
    {
        $all = [];
        foreach ($this->groups as $group) {
            foreach ($group->definitions() as $definition) {
                $all[] = $definition;
            }
        }

        return $all;
    }

    /**
     * Jalankan satu tool call dari AI.
     *
     * [FIX] SELALU kembalikan string JSON {success, message|error}
     * agar GroqChat bisa mendeteksi kegagalan dengan konsisten.
     *
     * @return array{0: string, 1: string} [hasil JSON untuk AI, kalimat ringkas untuk user (fallback)]
     */
    public function run(string $name, array $arguments, ?Authenticatable $user): array
    {
        $group = $this->groupFor($name);

        try {
            if (! $user) {
                $result = $this->fail(
                    'User belum login. Minta user untuk login terlebih dahulu sebelum melakukan aksi ini.'
                );
            } elseif (! $group) {
                $result = $this->fail("Fungsi tidak dikenal: {$name}");
            } else {
                // Hasil dari tool group SUDAH berformat JSON (diperbaiki di BookingTools dst.)
                $result = $group->run($name, $arguments, $user);

                // [FIX] Pengaman: kalau tool masih return string mentah (mis. tool lama belum di-patch),
                // bungkus jadi JSON sukses agar konsisten.
                if (! $this->isJson($result)) {
                    $result = $this->ok((string) $result);
                }
            }
        } catch (ValidationException $e) {
            $errors = collect($e->errors())->flatten()->implode('; ');
            $result = $this->fail("Data tidak valid — {$errors}");
        } catch (\Throwable $e) {
            Log::error('Tool call gagal', [
                'function' => $name,
                'arguments' => $arguments,
                'message' => $e->getMessage(),
            ]);
            $result = $this->fail('Gagal menjalankan fungsi: '.$e->getMessage());
        }

        $result = (string) $result;

        // Summary untuk user (dipakai GroqChat sebagai fallback kalau follow-up AI gagal)
        $summary = $group
            ? $group->describe($name, $arguments, $result)
            : $this->extractMessage($result);

        // [FIX] Kalau summary masih berupa string JSON mentah, decode agar enak dibaca user
        if ($this->isJson($summary)) {
            $summary = $this->extractMessage($summary);
        }

        return [$result, $summary];
    }

    private function groupFor(string $name): ?ToolGroup
    {
        foreach ($this->groups as $group) {
            if ($group->handles($name)) {
                return $group;
            }
        }

        return null;
    }

    // ── Helper JSON ────────────────────────────────────────────────

    private function ok(string $message): string
    {
        return json_encode([
            'success' => true,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function fail(string $error): string
    {
        return json_encode([
            'success' => false,
            'error' => $error,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function isJson(string $text): bool
    {
        if ($text === '') {
            return false;
        }
        json_decode($text);
        return json_last_error() === JSON_ERROR_NONE
            && in_array($text[0] ?? '', ['{', '['], true);
    }

    /**
     * Ambil pesan dari string JSON: {success, message} / {success, error}
     * Fallback ke string asli kalau bukan JSON.
     */
    private function extractMessage(string $result): string
    {
        $data = json_decode($result, true);
        if (is_array($data)) {
            return (string) ($data['message'] ?? $data['error'] ?? $result);
        }
        return $result;
    }
}