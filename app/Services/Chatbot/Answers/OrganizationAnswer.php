<?php

namespace App\Services\Chatbot\Answers;

use App\Models\User;

/**
 * LAPIS 4: Data pegawai dan organisasi.
 */
class OrganizationAnswer
{
    /**
     * Pertanyaan pegawai / organisasi.
     */
    public function answer(string $lower): ?string
    {
        $organizationTerms = ['user', 'pegawai', 'orang', 'karyawan', 'kepala', 'sekretaris', 'sekretariat', 'jabatan', 'bidang', 'staf'];
        $isOrganizationQuestion = collect($organizationTerms)->contains(fn (string $term): bool => str_contains($lower, $term));

        if (! $isOrganizationQuestion) {
            return null;
        }

        $users = User::query()
            ->where('status', 'aktif')
            ->where(function ($query): void {
                $query->where('role', '!=', 'admin')->orWhereNull('role');
            })
            ->get(['name', 'jabatan', 'bidang', 'username']);

        if ($users->isEmpty()) {
            return 'Belum ada data pegawai aktif di database.';
        }

        $asksCount = preg_match('/\b(berapa|jumlah|total|ada)\b/iu', $lower) === 1;
        if ($asksCount && preg_match('/\b(user|pegawai|orang|karyawan|staf)\b/iu', $lower) === 1) {
            return "Ada {$users->count()} pegawai aktif yang tercatat di database.";
        }

        if (preg_match('/\b(kepala|pimpinan|kepala badan)\b/iu', $lower) === 1) {
            $leaders = $users->filter(fn (User $user): bool => str_contains(mb_strtolower((string) $user->bidang), 'kepala badan')
                || str_contains(mb_strtolower((string) $user->jabatan), 'kepala badan')
            );

            return $leaders->isEmpty()
                ? 'Data kepala badan belum ditemukan di database.'
                : 'Kepala Badan: '.$leaders->map(fn (User $u): string => $u->name.' ('.($u->jabatan ?: 'jabatan belum diisi').')')->implode('; ').'.';
        }

        if (preg_match('/\b(sekretaris|sekretariat)\b/iu', $lower) === 1) {
            $secretaries = $users->filter(fn (User $user): bool => str_contains(mb_strtolower((string) $user->jabatan), 'sekretaris')
                || (str_contains(mb_strtolower((string) $user->bidang), 'sekretariat')
                    && str_contains(mb_strtolower((string) $user->jabatan), 'sekret'))
            );

            return $secretaries->isEmpty()
                ? 'Data sekretaris belum ditemukan di database.'
                : 'Sekretaris: '.$secretaries->map(fn (User $u): string => $u->name.' ('.($u->jabatan ?: 'jabatan belum diisi').')')->implode('; ').'.';
        }

        if (preg_match('/\b(bidang|jabatan)\b/iu', $lower) === 1) {
            return 'Data pegawai tersedia berdasarkan bidang dan jabatan. Sebutkan nama bidang atau jabatan yang ingin dicari.';
        }

        return null;
    }
}
