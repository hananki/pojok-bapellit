<?php

namespace App\Events;

use App\Models\Surat;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SuratSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Surat $surat)
    {
        // ⭐ OPTIMASI: Load relasi user di awal
        $this->surat->loadMissing('user');

        // Simpan notif ke database untuk Admin DAN Pengelola Surat
        try {
            // ⭐ Gunakan cache untuk daftar ID penerima (Admin & Pengelola Surat)
            $penerimaIds = Cache::remember('surat_notif_user_ids', 3600, function () {
                return User::whereIn('role', ['admin', 'pengelola_surat'])
                    ->orWhere('is_admin', true) // Jaga-jaga untuk user lama
                    ->pluck('id')
                    ->toArray();
            });

            // Ambil semua user penerima sekaligus (lebih efisien daripada find di dalam loop)
            $penerima = User::whereIn('id', $penerimaIds)->get();

            foreach ($penerima as $user) {
                $user->sendNotification(
                    'surat',
                    'Pengajuan Surat Baru',
                    "{$this->surat->no_surat} dari {$this->surat->user?->name}",
                    [
                        'surat_id' => $this->surat->id,
                        'no_surat' => $this->surat->no_surat,
                        'pengaju' => $this->surat->user?->name,
                        'jenis' => $this->surat->jenis_surat,
                        'created_at' => $this->surat->created_at,
                    ],
                    route('admin.kelolasurat.index')
                );
            }

            // Notif konfirmasi ke user pengaju
            if ($this->surat->user) {
                $this->surat->user->sendNotification(
                    'surat',
                    'Pengajuan Surat Berhasil',
                    "Surat {$this->surat->no_surat} ({$this->surat->jenis_surat}) berhasil diajukan.",
                    [
                        'surat_id' => $this->surat->id,
                        'no_surat' => $this->surat->no_surat,
                        'jenis' => $this->surat->jenis_surat,
                        'created_at' => $this->surat->created_at,
                    ],
                    route('surat.index')
                );
            }
        } catch (\Exception $e) {
            Log::warning('Gagal simpan notif surat: '.$e->getMessage());
        }
    }

    public function broadcastOn(): array
    {
        // Tetap gunakan channel admin, karena broadcast real-time 
        // akan difilter lagi di frontend berdasarkan role
        return [new PrivateChannel('admin.notifications')];
    }

    public function broadcastAs(): string
    {
        return 'surat.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->surat->id,
            'no_surat' => $this->surat->no_surat,
            'tanggal' => $this->surat->tanggal?->toDateString(),
            'no_indek' => $this->surat->no_indek,
            'jenis_surat' => $this->surat->jenis_surat,
            'sifat_surat' => $this->surat->sifat_surat,
            'asal_surat' => $this->surat->asal_surat,
            'alamat_tujuan' => $this->surat->alamat_tujuan,
            'isi_surat' => Str::limit($this->surat->isi_surat, 100),
            'keterangan' => $this->surat->keterangan,
            'lampiran' => $this->surat->banyak_lampiran,
            'file_url' => $this->surat->file_url,
            'is_pdf' => $this->surat->isFilePdf(),
            'pengaju' => $this->surat->user?->name,
            'created_at' => $this->surat->created_at?->diffForHumans(),
        ];
    }
}