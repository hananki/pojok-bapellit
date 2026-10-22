<?php

namespace App\Events;

use App\Models\AbsensiDetail;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AbsensiHadir implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public AbsensiDetail $detail)
    {
        // ⭐ OPTIMASI: Load relasi di awal untuk menghindari N+1
        $this->detail->loadMissing(['user', 'sesi']);

        try {
            // ⭐ Cache daftar ID penerima (Admin & Pengelola Absensi)
            $penerimaIds = Cache::remember('absensi_notif_user_ids', 3600, function () {
                return User::whereIn('role', ['admin', 'pengelola_absensi'])
                    ->orWhere('is_admin', true)
                    ->pluck('id')
                    ->toArray();
            });

            $namaSesi = $this->detail->sesi?->nama_sesi ?? 'Sesi Absensi';
            $namaUser = $this->detail->user?->name ?? 'Seseorang';
            $tanggal = $this->detail->sesi?->tanggal ?? now()->toDateString();

            // Notif ke admin + pengelola_absensi
            $penerima = User::whereIn('id', $penerimaIds)->get();

            foreach ($penerima as $user) {
                $user->sendNotification(
                    type: 'absensi',
                    title: 'Kehadiran Tercatat',
                    message: "{$namaUser} hadir di sesi \"{$namaSesi}\".",
                    data: [
                        'detail_id' => $this->detail->id,
                        'sesi_id' => $this->detail->absensi_sesi_id,
                        'nama_sesi' => $namaSesi,
                        'user_name' => $namaUser,
                        'tanggal' => $tanggal,
                        'waktu_absen' => $this->detail->waktu_absen?->format('H:i'),
                    ],
                    url: route('admin.absensi.show', $this->detail->absensi_sesi_id),
                );
            }

            // Notif konfirmasi ke user yang hadir
            if ($this->detail->user) {
                $this->detail->user->sendNotification(
                    type: 'absensi',
                    title: 'Absensi Berhasil',
                    message: "Kehadiran Anda di sesi \"{$namaSesi}\" ({$tanggal}) sudah tercatat.",
                    data: [
                        'detail_id' => $this->detail->id,
                        'sesi_id' => $this->detail->absensi_sesi_id,
                        'nama_sesi' => $namaSesi,
                        'tanggal' => $tanggal,
                        'waktu_absen' => $this->detail->waktu_absen?->format('H:i'),
                    ],
                    url: null,
                );
            }
        } catch (\Exception $e) {
            Log::warning('Gagal simpan notif absensi: '.$e->getMessage());
        }
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->detail->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'absensi.hadir';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->detail->id,
            'sesi' => $this->detail->sesi?->nama_sesi,
            'tanggal' => $this->detail->sesi?->tanggal,
            'waktu_absen' => $this->detail->waktu_absen?->format('H:i'),
            'user' => $this->detail->user?->name,
        ];
    }
}
