<?php

namespace App\Services\Chatbot\Tools;

use App\Models\AbsensiSesi;
use App\Models\AbsensiDetail;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;

class AbsensiHistoryTools implements ToolGroup
{
    private const TOOLS = [
        'get_absensi_sesi',
        'get_absensi_peserta',
        'get_absensi_saya',
    ];

    public function __construct(
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
                    'name' => 'get_absensi_sesi',
                    'description' => 'Cari daftar sesi absensi (bisa filter hari ini, kemarin, atau semua). Data dibatasi hanya 14 hari terakhir.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'filter_waktu' => ['type' => 'string', 'enum' => ['hari_ini', 'kemarin', 'semua'], 'description' => 'Filter waktu sesi (default: hari_ini).'],
                            'cari_nama' => ['type' => 'string', 'description' => 'Kata kunci nama sesi (opsional, misal: "ice breaking").'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_absensi_peserta',
                    'description' => 'Lihat daftar peserta yang belum absen atau tidak hadir di sesi tertentu. HANYA untuk Admin dan Pengelola Absensi.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'sesi_id' => ['type' => 'integer', 'description' => 'ID Sesi Absensi (didapat dari tool get_absensi_sesi).'],
                            'status' => ['type' => 'string', 'enum' => ['belum_absen', 'tidak_hadir', 'hadir'], 'description' => 'Status kehadiran yang ingin dicari.'],
                        ],
                        'required' => ['sesi_id', 'status'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_absensi_saya',
                    'description' => 'Lihat riwayat absensi milik user yang sedang login (berapa kali tidak masuk, sesi apa saja). Data dibatasi 14 hari terakhir.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'filter_status' => ['type' => 'string', 'enum' => ['hadir', 'tidak', 'semua'], 'description' => 'Filter status kehadiran (opsional).'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function run(string $name, array &$arguments, Authenticatable $user): string
    {
        $role = $user->role ?? 'user';
        $isAdminOrPengelola = in_array($role, ['admin', 'pengelola_absensi']);

        switch ($name) {
            case 'get_absensi_sesi':
                // Semua user boleh melihat daftar sesi (dibatasi 14 hari terakhir)
                return $this->getSesi($arguments);

            case 'get_absensi_peserta':
                // HANYA Admin/Pengelola yang boleh lihat detail peserta
                if (! $isAdminOrPengelola) {
                    return 'Maaf, fitur ini hanya untuk Admin dan Pengelola Absensi.';
                }
                return $this->getPeserta($arguments);

            case 'get_absensi_saya':
                // Semua user boleh lihat riwayatnya sendiri
                return $this->getAbsensiSaya($user, $arguments);

            default:
                return "Fungsi tidak dikenal: {$name}";
        }
    }

    public function describe(string $name, array $arguments, string $result): string
    {
        return $result;
    }

    // ── Implementasi ──────────────────────────────────────────────

    private function getSesi(array $args): string
    {
        $query = AbsensiSesi::query();
        $filter = $args['filter_waktu'] ?? 'hari_ini';

        // Batasi hanya 14 hari terakhir agar user ga kebanjiran data
        $query->where('tanggal', '>=', today()->subDays(14));

        if ($filter === 'hari_ini') {
            $query->whereDate('tanggal', today());
        } elseif ($filter === 'kemarin') {
            $query->whereDate('tanggal', today()->subDay());
        }

        if (! empty($args['cari_nama'])) {
            $query->where('nama_sesi', 'like', '%' . $args['cari_nama'] . '%');
        }

        $sesi = $query->orderBy('tanggal', 'desc')->limit(10)->get();

        if ($sesi->isEmpty()) {
            return 'Belum ada sesi absensi dalam 14 hari terakhir untuk filter tersebut. Jika kamu mencari data yang lebih lama, silakan buka menu "Presensi Saya" di sidebar untuk melihat riwayat lengkap.';
        }

        $lines = $sesi->map(fn($s) => "- [ID {$s->id}] {$s->nama_sesi} (Tanggal: {$s->tanggal->format('d-m-Y')}, Lokasi: {$s->lokasi})");
        return "Ditemukan " . $sesi->count() . " sesi (maksimal 14 hari terakhir):\n" . $lines->implode("\n") . "\n\nUntuk riwayat yang lebih lengkap, silakan buka menu **Presensi Saya**.";
    }

    private function getPeserta(array $args): string
    {
        $sesiId = $args['sesi_id'];
        $status = $args['status'];

        $sesi = AbsensiSesi::find($sesiId);
        if (! $sesi) {
            return 'Sesi absensi tidak ditemukan.';
        }

        $query = AbsensiDetail::with('user:id,name')
            ->where('absensi_sesi_id', $sesiId);

        if ($status === 'belum_absen') {
            $query->whereNull('status_kehadiran');
        } elseif ($status === 'tidak_hadir') {
            $query->where('status_kehadiran', 'tidak');
        } elseif ($status === 'hadir') {
            $query->where('status_kehadiran', 'hadir');
        }

        $peserta = $query->get();

        if ($peserta->isEmpty()) {
            return "Tidak ada peserta dengan status '{$status}' di sesi \"{$sesi->nama_sesi}\".";
        }

        $names = $peserta->map(fn($p) => $p->user->name ?? 'User Dihapus');
        return "Ada {$peserta->count()} peserta dengan status '{$status}' di sesi \"{$sesi->nama_sesi}\":\n- " . $names->implode("\n- ");
    }

    private function getAbsensiSaya(Authenticatable $user, array $args): string
    {
        $query = AbsensiDetail::with('sesi')
            ->where('user_id', $user->getAuthIdentifier())
            ->whereHas('sesi', function($q) {
                // Batasi 14 hari terakhir
                $q->where('tanggal', '>=', today()->subDays(14));
            });

        if (isset($args['filter_status']) && $args['filter_status'] !== 'semua') {
            $query->where('status_kehadiran', $args['filter_status']);
        }

        $details = $query->orderBy('created_at', 'desc')->limit(10)->get();

        if ($details->isEmpty()) {
            return 'Kamu belum punya riwayat absensi dalam 14 hari terakhir. Jika ingin melihat riwayat lengkap, silakan buka menu **Presensi Saya**.';
        }

        // Hitung statistik 14 hari terakhir
        $baseQuery = AbsensiDetail::where('user_id', $user->getAuthIdentifier())
            ->whereHas('sesi', fn($q) => $q->where('tanggal', '>=', today()->subDays(14)));
        
        $totalHadir = (clone $baseQuery)->where('status_kehadiran', 'hadir')->count();
        $totalTidak = (clone $baseQuery)->where('status_kehadiran', 'tidak')->count();
        
        $lines = $details->map(function ($d) {
            $status = $d->status_kehadiran ?? 'Belum Diabsen';
            $tgl = $d->sesi?->tanggal ? Carbon::parse($d->sesi->tanggal)->format('d-m-Y') : '-';
            $nama = $d->sesi?->nama_sesi ?? 'Sesi Dihapus';
            return "- {$nama} ({$tgl}): {$status}";
        });

        return "Riwayat absensi kamu (14 hari terakhir) — Hadir: {$totalHadir}, Tidak Hadir: {$totalTidak}:\n" 
            . $lines->implode("\n") 
            . "\n\nIngin lihat riwayat lengkap? Buka menu **Presensi Saya** di sidebar ya.";
    }
}