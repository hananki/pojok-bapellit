<?php

namespace App\Services\Chatbot\Tools;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class UserManagementTools implements ToolGroup
{
    public function handles(string $name): bool
    {
        return $name === 'kelola_user';
    }

    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'kelola_user',
                    'description' => 'Mengelola dan mencari data user/pegawai di database. KHUSUS ADMIN.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'aksi' => [
                                'type' => 'string',
                                'enum' => ['hitung_user', 'cari_user', 'ubah_role', 'hapus_akun'],
                                'description' => 'Aksi yang mau dilakukan',
                            ],
                            'kata_kunci' => [
                                'type' => 'string',
                                'description' => 'Nama, email, atau NIP user (wajib untuk cari/ubah/hapus)',
                            ],
                            'role' => [
                                'type' => 'string',
                                'description' => 'Filter role (contoh: admin, user, pengelola_surat, pengelola_aula, pengelola_absensi). Kosongkan jika tidak difilter.',
                            ],
                            'role_baru' => [
                                'type' => 'string',
                                'description' => 'Role baru (wajib untuk ubah_role)',
                            ],
                        ],
                        'required' => ['aksi'],
                    ],
                ],
            ],
        ];
    }

    public function run(string $name, array &$arguments, Authenticatable $user): string
    {
        // 1. VALIDASI ROLE: Hanya admin
        if (($user->role ?? '') !== 'admin') {
            return $this->fail('Akses ditolak. Fitur kelola user hanya untuk Admin.');
        }

        $aksi = $arguments['aksi'] ?? '';
        $kataKunci = $arguments['kata_kunci'] ?? '';
        $roleFilter = $arguments['role'] ?? '';
        $roleBaru = $arguments['role_baru'] ?? '';

        // 2. Eksekusi Aksi
        if ($aksi === 'hitung_user') {
            $query = User::query();
            if (!empty($roleFilter)) {
                $query->where('role', $roleFilter);
            }
            $total = $query->count();
            $label = !empty($roleFilter) ? "dengan role {$roleFilter}" : "total";
            return $this->ok("Jumlah user {$label} ada {$total} orang.");
        }

        if ($aksi === 'cari_user') {
            if (empty($kataKunci) && empty($roleFilter)) {
                return $this->fail('Kata kunci nama/email/NIP atau filter role wajib diisi.');
            }
            
            $query = User::query();
            if (!empty($kataKunci)) {
                $query->where(function($q) use ($kataKunci) {
                    $q->where('name', 'like', "%{$kataKunci}%")
                      ->orWhere('email', 'like', "%{$kataKunci}%")
                      ->orWhere('nip', 'like', "%{$kataKunci}%");
                });
            }
            if (!empty($roleFilter)) {
                $query->where('role', $roleFilter);
            }

            $users = $query->take(10)->get(); // Batasi 10 biar ga kepanjangan

            if ($users->isEmpty()) {
                return $this->ok("User tidak ditemukan.");
            }

            $pesan = "Ditemukan {$users->count()} user:\n";
            foreach ($users as $u) {
                $pesan .= "- {$u->name} (Role: {$u->role}, Bidang: " . ($u->bidang ?? '-') . ")\n";
            }
            return $this->ok(trim($pesan));
        }

        if (in_array($aksi, ['ubah_role', 'hapus_akun'])) {
            if (empty($kataKunci)) {
                return $this->fail('Nama atau email user target wajib disebutkan.');
            }

            $targetUser = User::where('name', $kataKunci)->orWhere('email', $kataKunci)->first();
            
            if (! $targetUser) {
                return $this->fail("User '{$kataKunci}' tidak ditemukan.");
            }

            if ($aksi === 'ubah_role') {
                if (empty($roleBaru)) {
                    return $this->fail('Role baru wajib disebutkan (contoh: admin, pengelola_aula, user).');
                }
                $targetUser->update([
                    'role' => $roleBaru,
                    'is_admin' => $roleBaru === 'admin' // Samain kayak di controller lu
                ]);
                return $this->ok("Berhasil mengubah role user '{$targetUser->name}' menjadi '{$roleBaru}'.");
            }

            if ($aksi === 'hapus_akun') {
                if ($targetUser->id === $user->getAuthIdentifier()) {
                    return $this->fail('Kamu tidak bisa menghapus akunmu sendiri.');
                }
                $nama = $targetUser->name;
                $targetUser->delete();
                return $this->ok("Akun user '{$nama}' berhasil dihapus.");
            }
        }

        return $this->fail('Aksi tidak valid.');
    }

    public function describe(string $name, array $arguments, string $result): string
    {
        $data = json_decode($result, true);
        if (is_array($data) && isset($data['message'])) {
            return $data['message'];
        }
        if (is_array($data) && isset($data['error'])) {
            return '⚠️ ' . $data['error'];
        }
        return 'Aksi kelola user telah diproses.';
    }

    private function ok(string $message): string
    {
        return json_encode(['success' => true, 'message' => $message], JSON_UNESCAPED_UNICODE);
    }

    private function fail(string $error): string
    {
        return json_encode(['success' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
    }
}