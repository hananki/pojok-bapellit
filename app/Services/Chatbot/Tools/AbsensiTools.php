<?php

namespace App\Services\Chatbot\Tools;

use App\Services\Chatbot\Support\DateParser;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Tool AI untuk ABSENSI:
 *  - create_absensi : buat sesi absensi baru (Ice Breaking)
 */
class AbsensiTools implements ToolGroup
{
    private const TOOLS = ['create_absensi'];

    public function __construct(
        private DateParser $dates,
        private ToolSupport $support,
    ) {
    }

    public function handles(string $name): bool
    {
        return in_array($name, self::TOOLS, true);
    }

    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_absensi',
                    'description' => 'Buat sesi absensi baru (Ice Breaking).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'nama_sesi' => ['type' => 'string'],
                            'tanggal' => ['type' => 'string', 'description' => 'Format YYYY-MM-DD'],
                            'lokasi' => ['type' => 'string'],
                            'catatan' => ['type' => 'string'],
                            'pilih_user_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array ID user yang ikut. Jika kosong, semua user aktif.'],
                        ],
                        'required' => ['nama_sesi', 'tanggal'],
                    ],
                ],
            ],
        ];
    }

    public function run(string $name, array &$arguments, Authenticatable $user): string
    {
        if ($name !== 'create_absensi') {
            return "Fungsi tidak dikenal: {$name}";
        }

        $arguments = $this->dates->normalizeOptionalDate($arguments, 'tanggal');

        $request = $this->support->makeRequest($arguments, $user);

        // Namespace yang benar: App\Http\Controllers\Admin\AbsensiController
        $response = (new \App\Http\Controllers\Admin\AbsensiController())->store($request);

        return $this->support->summarize($response, 'Sesi absensi berhasil dibuat.');
    }

    public function describe(string $name, array $arguments, string $result): string
    {
        return $result;
    }
}
