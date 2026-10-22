<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookingCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Booking $booking)
    {
        // ⭐ OPTIMASI: Load relasi sekali di awal untuk menghindari N+1 di dalam loop
        $this->booking->loadMissing(['user', 'aula']);

        // Simpan notif ke database untuk Admin DAN Pengelola Aula
        try {
            // ⭐ Cache daftar ID penerima (Admin & Pengelola Aula)
            $penerimaIds = Cache::remember('booking_notif_user_ids', 3600, function () {
                return User::whereIn('role', ['admin', 'pengelola_aula'])
                    ->orWhere('is_admin', true) // Jaga-jaga untuk user lama
                    ->pluck('id')
                    ->toArray();
            });

            // Ambil semua user penerima sekaligus (lebih efisien daripada find di dalam loop)
            $penerima = User::whereIn('id', $penerimaIds)->get();

            foreach ($penerima as $user) {
                $user->sendNotification(
                    type: 'booking',
                    title: 'Booking Baru',
                    message: "{$this->booking->nama_penanggung_jawab} booking {$this->booking->aula?->nama}",
                    data: [
                        'booking_id' => $this->booking->id,
                        'aula' => $this->booking->aula?->nama,
                        'tanggal' => $this->booking->tanggal_booking,
                        'sesi' => $this->booking->sesi_waktu,
                    ],
                    url: route('admin.kelolabooking.index'),
                );
            }

            // Notif konfirmasi ke user pengaju booking
            if ($this->booking->user) {
                $this->booking->user->sendNotification(
                    type: 'booking',
                    title: 'Booking Berhasil Dikirim',
                    message: "Booking aula {$this->booking->aula?->nama} tanggal {$this->booking->tanggal_booking} sedang menunggu persetujuan.",
                    data: [
                        'booking_id' => $this->booking->id,
                        'aula' => $this->booking->aula?->nama,
                        'tanggal' => $this->booking->tanggal_booking,
                        'sesi' => $this->booking->sesi_waktu,
                    ],
                    url: route('riwayat.index'),
                );
            }
        } catch (\Exception $e) {
            // Kalau gagal simpan notif, jangan bikin booking gagal
            Log::warning('Gagal simpan notif booking: '.$e->getMessage());
        }
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.notifications')];
    }

    public function broadcastAs(): string
    {
        return 'booking.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->booking->id,
            'nama' => $this->booking->nama_penanggung_jawab,
            'keperluan' => $this->booking->keperluan,
            'tanggal' => $this->booking->tanggal_booking,
            'sesi' => $this->booking->sesi_waktu,
            'peserta' => $this->booking->jumlah_peserta,
            'status' => $this->booking->status,
            'catatan' => $this->booking->catatan,
            'pengaju' => $this->booking->user?->name,
            'aula' => $this->booking->aula?->nama,
            'lokasi' => $this->booking->aula?->lokasi,
            'created_at' => $this->booking->created_at?->diffForHumans(),
        ];
    }
}