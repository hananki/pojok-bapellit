<?php

namespace App\Services\Chatbot\Tools;

use App\Services\Chatbot\Support\DateParser;
use Illuminate\Contracts\Auth\Authenticatable;

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
                    // DESKRIPSI DIBUAT NETRAL TANPA MENYEBUT ROLE
                    'description' => "Buat sesi absensi baru (Ice Breaking). HANYA panggil fungsi ini jika user memberikan PERINTAH JELAS untuk membuat sesi (contoh: 'tolong buat sesi', 'bikin sesi baru'). JANGAN panggil fungsi ini jika user hanya BERTANYA 'ada sesi apa?', 'lihat sesi', atau 'riwayat sesi'.",
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'nama_sesi' => ['type' => 'string', 'description' => 'Nama sesi absensi (misal: Rapat Koordinasi, Ice Breaking)'],
                            'tanggal' => ['type' => 'string', 'description' => 'Format YYYY-MM-DD'],
                            'lokasi' => ['type' => 'string', 'description' => 'Lokasi pelaksanaan sesi'],
                            'catatan' => ['type' => 'string', 'description' => 'Keterangan tambahan (opsional)'],
                            'pilih_bidang' => [
                                'type' => 'array', 
                                'items' => ['type' => 'string'], 
                                'description' => 'Opsional. Array nama bidang (HURUF BESAR). Contoh: ["SEKRETARIAT", "KEPALA BADAN"]. Jika user memilih semua, kosongkan.'
                            ],
                            'pilih_jabatan' => [
                                'type' => 'array', 
                                'items' => ['type' => 'string'], 
                                'description' => 'Opsional. Array nama jabatan (HURUF BESAR).'
                            ],
                            'pilih_user_ids' => [
                                'type' => 'array', 
                                'items' => ['type' => 'integer'], 
                                'description' => 'Opsional. Array ID user spesifik.'
                            ],
                        ],
                        'required' => ['nama_sesi', 'tanggal', 'lokasi'],
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

        // 1. CEK ROLE (Hanya di sini pengecekan role dilakukan)
        $role = $user->role ?? 'user';
        if (! in_array($role, ['admin', 'pengelola_absensi'])) {
            $roleLabel = match($role) {
                'pengelola_surat' => 'Pengelola Surat',
                'pengelola_aula' => 'Pengelola Aula',
                'user' => 'User Biasa / Pegawai',
                default => ucfirst(str_replace('_', ' ', $role)),
            };
            
            return "Maaf, akun Anda saat ini memiliki role {$roleLabel}, sehingga tidak memiliki akses untuk membuat sesi absensi. Fitur ini hanya tersedia untuk Admin dan Pengelola Absensi. Silakan hubungi Admin atau Pengelola Absensi jika Anda memerlukan bantuan.";
        }

        // 2. NORMALISASI TANGGAL
        $arguments = $this->dates->normalizeOptionalDate($arguments, 'tanggal');

        // 3. BUAT REQUEST & PANGGIL CONTROLLER
        $request = $this->support->makeRequest($arguments, $user);

        $response = (new \App\Http\Controllers\Admin\AbsensiController())->storeViaChatbot($request);

        return $this->support->summarize($response, 'Sesi absensi berhasil dibuat.');
    }

    public function describe(string $name, array $arguments, string $result): string
    {
        return $result;
    }
}