<?php

namespace App\Http\Controllers;

use App\Models\AbsensiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    /**
     * Halaman Presensi Saya — riwayat absen user yang login
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        $filterStatus = $request->input('status'); // hadir / tidak / kosong

        // Ambil semua detail absensi user ini + info sesinya
        $query = AbsensiDetail::with(['sesi:id,nama_sesi,tanggal'])
            ->where('user_id', $user->id)
            ->whereHas('sesi') // pastikan sesi-nya masih ada (tidak dihapus)
            ->when($search, function ($q, $search) {
                return $q->whereHas('sesi', function ($sub) use ($search) {
                    $sub->where('nama_sesi', 'like', "%{$search}%");
                });
            })
            ->when($filterStatus, function ($q, $filterStatus) {
                return $q->where('status_kehadiran', $filterStatus);
            });

        // Riwayat absensi (pagination)
        $riwayat = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // ⭐ OPTIMASI: Hitung statistik dalam 1 query saja menggunakan conditional aggregation
        $baseQuery = AbsensiDetail::where('user_id', $user->id)
            ->whereHas('sesi');

        // Gunakan select raw untuk menghitung semuanya sekaligus
        $stats = $baseQuery->selectRaw("
            COUNT(*) as total_sesi,
            SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
            SUM(CASE WHEN status_kehadiran = 'tidak' THEN 1 ELSE 0 END) as total_tidak,
            SUM(CASE WHEN status_kehadiran IS NULL THEN 1 ELSE 0 END) as total_belum,
            SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? AND status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as hadir_bulan_ini
        ", [now()->month, now()->year])->first();

        $totalSesi = $stats->total_sesi ?? 0;
        $totalHadir = $stats->total_hadir ?? 0;
        $totalTidak = $stats->total_tidak ?? 0;
        $totalBelumDiabsen = $stats->total_belum ?? 0;
        $hadirBulanIni = $stats->hadir_bulan_ini ?? 0;

        // Persentase kehadiran
        $totalDiabsen = $totalHadir + $totalTidak;
        $persentaseHadir = $totalDiabsen > 0
            ? round(($totalHadir / $totalDiabsen) * 100, 1)
            : 0;

        // Data untuk kalender (semua tanpa pagination)
        $kalenderData = AbsensiDetail::with(['sesi:id,nama_sesi,tanggal'])
            ->where('user_id', $user->id)
            ->whereHas('sesi')
            ->get()
            ->map(fn ($r) => [
                'id'          => $r->id,
                'nama'        => $r->sesi->nama_sesi,
                'tgl'         => $r->sesi->tanggal->format('Y-m-d'),
                // [FIX] Tambahan agar pop-up modal bisa menampilkan data lengkap
                'tanggal_fmt' => $r->sesi->tanggal->translatedFormat('d M Y'),
                'waktu'       => $r->waktu_absen
                    ? \Carbon\Carbon::parse($r->waktu_absen)->format('H:i')
                    : null,
                'keterangan'  => $r->keterangan,
                // ------------------------------------------------------------------
                'status'      => $r->status_kehadiran,
            ]);

        // Data untuk tabel client-side (semua, tanpa pagination)
        $tabelData = AbsensiDetail::with(['sesi:id,nama_sesi,tanggal'])
            ->where('user_id', $user->id)
            ->whereHas('sesi')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'          => $r->id,
                'nama_sesi'   => $r->sesi->nama_sesi,
                'tanggal_fmt' => $r->sesi->tanggal->translatedFormat('d M Y'),
                'waktu'       => $r->waktu_absen
                    ? \Carbon\Carbon::parse($r->waktu_absen)->format('H:i')
                    : null,
                'status'      => $r->status_kehadiran,
                'keterangan'  => $r->keterangan,
            ]);

        return view('presensi.index', compact(
            'riwayat',
            'search',
            'filterStatus',
            'totalSesi',
            'totalHadir',
            'totalTidak',
            'totalBelumDiabsen',
            'persentaseHadir',
            'hadirBulanIni',
            'kalenderData',
            'tabelData'
        ));
    }
}