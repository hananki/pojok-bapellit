<?php

namespace App\Services\Chatbot\Tools;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Daftar semua tool AI. Menggabungkan definisi tool dan menjalankan tool yang dipanggil AI.
 *
 * Menambah domain baru (misal: kendaraan, cuti):
 *   1. buat class XxxTools implements ToolGroup di folder ini
 *   2. tambahkan ke constructor di bawah
 */
class ToolRegistry
{
    /** @var ToolGroup[] */
    private array $groups;

    public function __construct(
        BookingTools $booking,
        AbsensiTools $absensi,
        SuratTools $surat,
    ) {
        $this->groups = [$booking, $absensi, $surat];
    }

    /**
     * Semua definisi tool untuk dikirim ke Groq.
     */
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
     * @return array{0: string, 1: string} [hasil untuk AI, kalimat ringkas untuk user (fallback)]
     */
    public function run(string $name, array $arguments, ?Authenticatable $user): array
    {
        $group = $this->groupFor($name);

        try {
            if (! $user) {
                // Semua aksi sistem butuh user yang sedang login
                $result = 'Gagal: user belum login. Minta user untuk login terlebih dahulu sebelum melakukan aksi ini.';
            } elseif (! $group) {
                $result = "Fungsi tidak dikenal: {$name}";
            } else {
                $result = $group->run($name, $arguments, $user);
            }
        } catch (ValidationException $e) {
            $result = "Gagal menjalankan fungsi {$name}: data tidak valid — "
                .collect($e->errors())->flatten()->implode('; ');
        } catch (\Throwable $e) {
            Log::error('Tool call gagal', [
                'function' => $name,
                'arguments' => $arguments,
                'message' => $e->getMessage(),
            ]);
            $result = "Gagal menjalankan fungsi {$name}: ".$e->getMessage();
        }

        $result = (string) $result;
        $summary = $group ? $group->describe($name, $arguments, $result) : $result;

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
}
