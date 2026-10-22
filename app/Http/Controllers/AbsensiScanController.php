<?php

namespace App\Http\Controllers;

use App\Events\AbsensiHadir;
use App\Models\AbsensiDetail;
use App\Models\AbsensiSesi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiScanController extends Controller
{
    /**
     * Halaman scan QR (untuk user)
     */
    public function showScan()
    {
        return view('presensi.scan');
    }

    /**
     * Handle scan via URL token (dari QR di-scan browser HP)
     */
    public function scanViaToken(Request $request, string $token)
    {
        $user = Auth::user();
        $sesi = AbsensiSesi::where('token_qr', $token)->first();

        // ============================================
        // QR tidak valid
        // ============================================
        if (! $sesi) {
            return view('presensi.scan-result', [
                'status' => 'error',
                'title' => 'QR Code Tidak Valid',
                'message' => 'QR Code tidak dikenali. Silakan minta admin menampilkan QR baru.',
            ]);
        }

        $tanggalSesi = Carbon::parse($sesi->tanggal)->translatedFormat('d F Y');

        // ============================================
        // QR kadaluarsa
        // ============================================
        if (! $sesi->isQrValid()) {
            return view('presensi.scan-result', [
                'status' => 'error',
                'title' => 'QR Code Kadaluarsa',
                'message' => 'QR Code untuk sesi "'.$sesi->nama_sesi.'" ('.$tanggalSesi.') sudah tidak berlaku. '
                           .'Kemungkinan sudah di-refresh oleh admin. Silakan scan QR terbaru di layar admin.',
                'sesi' => $sesi,
            ]);
        }

        // ============================================
        // Sesi sudah dikunci
        // ============================================
        if ($sesi->is_locked) {
            return view('presensi.scan-result', [
                'status' => 'error',
                'title' => 'Sesi Sudah Dikunci',
                'message' => 'Absensi untuk sesi "'.$sesi->nama_sesi.'" ('.$tanggalSesi.') sudah ditutup oleh admin. '
                           .'Tidak bisa absen lagi.',
                'sesi' => $sesi,
            ]);
        }

        // ============================================
        // CEK: user terdaftar di sesi ini?
        // ============================================
        $detail = AbsensiDetail::where('absensi_sesi_id', $sesi->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $detail) {
            return view('presensi.scan-result', [
                'status' => 'error',
                'title' => 'Anda Tidak Terdaftar',
                'message' => 'Anda tidak terdaftar sebagai peserta di sesi "'.$sesi->nama_sesi.'" ('.$tanggalSesi.'). '
                           .'Kemungkinan admin tidak memilih Anda saat membuat sesi ini. '
                           .'Silakan hubungi admin untuk didaftarkan.',
                'sesi' => $sesi,
            ]);
        }

        // ============================================
        // Sudah pernah scan
        // ============================================
        if ($detail->status_kehadiran === 'hadir') {
            $waktuAbsen = $detail->waktu_absen
                ? $detail->waktu_absen->translatedFormat('d F Y, H:i').' WIB'
                : '-';

            return view('presensi.scan-result', [
                'status' => 'info',
                'title' => 'Sudah Absen',
                'message' => 'Anda sudah tercatat hadir di sesi "'.$sesi->nama_sesi.'" pada '.$waktuAbsen.'.',
                'sesi' => $sesi,
                'waktu' => $detail->waktu_absen,
            ]);
        }

        // ============================================
        // Update ke HADIR
        // ============================================
        $detail->update([
            'status_kehadiran' => 'hadir',
            'waktu_absen' => now(),
            'keterangan' => null,
        ]);

        // Kirim notif ke admin, pengelola_absensi, dan user yang hadir
        broadcast(new AbsensiHadir($detail->fresh(['user', 'sesi'])));

        return view('presensi.scan-result', [
            'status' => 'success',
            'title' => 'Absensi Berhasil!',
            'message' => 'Kehadiran Anda di sesi "'.$sesi->nama_sesi.'" ('.$tanggalSesi.') sudah tercatat. Terima kasih.',
            'sesi' => $sesi,
            'waktu' => now(),
        ]);
    }

    /**
     * Proses scan via AJAX (dari kamera HTML5 QR)
     */
    public function processScan(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $user = Auth::user();
        $sesi = AbsensiSesi::where('token_qr', $request->token)->first();

        // ============================================
        // QR tidak valid
        // ============================================
        if (! $sesi) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau tidak dikenali.',
            ], 404);
        }

        $tanggalSesi = Carbon::parse($sesi->tanggal)->translatedFormat('d F Y');

        // ============================================
        // QR kadaluarsa
        // ============================================
        if (! $sesi->isQrValid()) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code untuk sesi "'.$sesi->nama_sesi.'" sudah kadaluarsa. Silakan scan QR terbaru di layar admin.',
            ], 410);
        }

        // ============================================
        // Sesi dikunci
        // ============================================
        if ($sesi->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi "'.$sesi->nama_sesi.'" sudah dikunci oleh admin. Tidak bisa absen lagi.',
            ], 423);
        }

        // ============================================
        // CEK: user terdaftar?
        // ============================================
        $detail = AbsensiDetail::where('absensi_sesi_id', $sesi->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $detail) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar sebagai peserta di sesi "'.$sesi->nama_sesi.'" ('.$tanggalSesi.'). Hubungi admin untuk didaftarkan.',
            ], 403);
        }

        // ============================================
        // Sudah absen
        // ============================================
        if ($detail->status_kehadiran === 'hadir') {
            $waktuAbsen = $detail->waktu_absen
                ? $detail->waktu_absen->translatedFormat('d F Y, H:i').' WIB'
                : '-';

            return response()->json([
                'success' => true,
                'already' => true,
                'message' => 'Anda sudah tercatat hadir di sesi ini pada '.$waktuAbsen.'.',
                'sesi' => $sesi->nama_sesi,
                'waktu' => $detail->waktu_absen?->format('H:i:s'),
            ]);
        }

        // ============================================
        // Update kehadiran
        // ============================================
        $detail->update([
            'status_kehadiran' => 'hadir',
            'waktu_absen' => now(),
            'keterangan' => null,
        ]);

        // Kirim notif ke admin, pengelola_absensi, dan user yang hadir
        broadcast(new AbsensiHadir($detail->fresh(['user', 'sesi'])));

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil! Kehadiran Anda di sesi "'.$sesi->nama_sesi.'" sudah tercatat.',
            'sesi' => $sesi->nama_sesi,
            'waktu' => now()->format('H:i:s'),
        ]);
    }
}
