<?php

use App\Http\Controllers\AbsensiScanController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminSuratController;
use App\Http\Controllers\Admin\AulaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KendaraanController;
use App\Http\Controllers\Admin\NotificationStreamController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\SuratController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========== HALAMAN UTAMA ==========
Route::get('/', function () {
    return redirect()->route('booking.index');
});

// ========== HALAMAN AKUN NONAKTIF ==========
Route::get('/akun-nonaktif', function () {
    return view('auth.akun-nonaktif');
})->name('akun.nonaktif');

// ========== AUTH ROUTES ==========
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ==========================================================
// ========== ROUTE USER (auth + cek.status) ================
// ==========================================================
Route::middleware(['auth', 'cek.status'])->group(function () {

    // ===== AKUN USER =====
    Route::get('/akun', [AkunController::class, 'edit'])->name('akun.edit');
    Route::patch('/akun', [AkunController::class, 'update'])->name('akun.update');
    Route::put('/akun/password', [AkunController::class, 'updatePassword'])->name('password.update');
    Route::post('/akun/photo', [AkunController::class, 'updatePhoto'])->name('akun.photo.update');

    // ===== COMPATIBILITY ROUTE PROFILE LAMA =====
    Route::get('/profile', [AkunController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AkunController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== EMAIL VERIFICATION =====
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'verification-link-already-sent');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    // ===== SCAN ABSENSI (USER) =====
    Route::get('/absensi/scan', [AbsensiScanController::class, 'showScan'])->name('absensi.scan-page');
    Route::get('/absensi/scan/{token}', [AbsensiScanController::class, 'scanViaToken'])->name('absensi.scan');
    Route::post('/absensi/scan/process', [AbsensiScanController::class, 'processScan'])->name('absensi.scan-process');

    // ===== BOOKING USER =====
    Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/booking/create/{id}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::post('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.check-availability');
    Route::get('/booking/{id}', [BookingController::class, 'detail'])->name('booking.detail');
    Route::post('/booking/{id}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');

    // ===== RIWAYAT BOOKING =====
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat/{id}', [RiwayatController::class, 'show'])->name('riwayat.show');
    Route::post('/riwayat/{id}/cancel', [RiwayatController::class, 'cancel'])->name('riwayat.cancel');

    // ===== AMBIL SURAT (USER) =====
    Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
    Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
    Route::get('/surat/preview-nomor', [SuratController::class, 'previewNomor'])->name('surat.preview-nomor');
    Route::put('/surat/{id}', [SuratController::class, 'update'])->name('surat.update');
    
    // ⚠️ URUTAN INI DIPINDAHKAN KE ATAS RUTE SHOW AGAR TIDAK 404
    Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');
    
    Route::get('/surat/{id}', [SuratController::class, 'show'])->name('surat.show');

    // ===== PRESENSI SAYA (USER) =====
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');

    // ===== NOTIFIKASI (USER & ADMIN) =====
    Route::get('/notifications/stream', [NotificationStreamController::class, 'stream'])
        ->name('notifications.stream');
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])
        ->name('notifications.destroy-all');

    // ===== HAPUS OTOMATIS NOTIFIKASI LEBIH DARI SATU BULAN =====
    Route::post('/notifications/cleanup-old', function (Request $request) {
        $deleted = $request->user()
            ->notifications()
            ->where('created_at', '<', now()->subMonth())
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    })->name('notifications.cleanup-old');

    // ===== PAJAK KENDARAAN (READ-ONLY untuk user) =====
    Route::get('/kendaraan', [KendaraanController::class, 'index'])->name('kendaraan.index');
    Route::get('/kendaraan/{id}', [KendaraanController::class, 'show'])->name('kendaraan.show');
    Route::post('/kendaraan/dismiss-alert', [KendaraanController::class, 'dismissAlert'])->name('kendaraan.dismiss-alert');

    // ===== CHATBOT AI (USER & ADMIN) =====
    Route::get('/chatbot/menus', [ChatbotController::class, 'menus'])
        ->name('chatbot.menus');
    Route::post('/chatbot/template', [ChatbotController::class, 'template'])
        ->middleware('throttle:30,1')
        ->name('chatbot.template');
    Route::post('/chatbot', [ChatbotController::class, 'chat'])
        ->middleware('throttle:30,1')
        ->name('chatbot.chat');
    Route::post('/chatbot/clear-history', [ChatbotController::class, 'clearHistory'])
        ->name('chatbot.clear-history');
});


// ==========================================================
// ========== ROUTE ADMIN & STAFF ===========================
// ==========================================================

// 0. RUTE DASHBOARD (Bisa diakses semua staf)
Route::middleware(['auth', 'cek.status', 'role:admin,pengelola_surat,pengelola_aula,pengelola_absensi'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ===== DASHBOARD =====
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    });


// 1. RUTE HANYA UNTUK ADMIN (Akses Penuh)
Route::middleware(['auth', 'cek.status', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ===== PENGATURAN SISTEM =====
        Route::get('/pengaturan', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/pengaturan/suara', [SettingsController::class, 'sound'])->name('settings.sound');
        Route::get('/pengaturan/sesi-perangkat', [SettingsController::class, 'sessions'])->name('settings.sessions');
        Route::post('/pengaturan/chatbot', [SettingsController::class, 'storeTemplate'])->name('settings.chatbot.store');
        Route::put('/pengaturan/chatbot/{template}', [SettingsController::class, 'updateTemplate'])->name('settings.chatbot.update');
        Route::delete('/pengaturan/chatbot/{template}', [SettingsController::class, 'destroyTemplate'])->name('settings.chatbot.destroy');
        Route::put('/pengaturan/suara', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::post('/pengaturan/suara/reset', [SettingsController::class, 'resetSound'])->name('settings.sound.reset');
        Route::delete('/pengaturan/sesi/semua', [SettingsController::class, 'destroyAllSessions'])->name('settings.sessions.destroy-all');
        Route::delete('/pengaturan/sesi-aktif/semua', [SettingsController::class, 'destroyAllActiveSessions'])->name('settings.sessions.destroy-all-active');
        Route::delete('/pengaturan/sesi/{history}', [SettingsController::class, 'destroySession'])->name('settings.sessions.destroy');
        Route::delete('/pengaturan/sesi-aktif/{id}', [SettingsController::class, 'destroyActiveSession'])->name('settings.sessions.destroy-active');
        Route::post('/pengaturan/sesi/ban', [SettingsController::class, 'banSession'])->name('settings.sessions.ban');
        Route::delete('/pengaturan/sesi-ban/{banned}', [SettingsController::class, 'unbanSession'])->name('settings.sessions.unban');

        // ===== PAJAK KENDARAAN (ADMIN) =====
        Route::get('/kendaraan', [KendaraanController::class, 'index'])->name('kendaraan.index');
        Route::get('/kendaraan/create', [KendaraanController::class, 'create'])->name('kendaraan.create');
        Route::post('/kendaraan', [KendaraanController::class, 'store'])->name('kendaraan.store');
        Route::post('/kendaraan/pajak/{pajakId}/tandai-bayar', [KendaraanController::class, 'tandaiBayar'])->name('kendaraan.tandai-bayar');
        Route::get('/kendaraan/{id}/edit', [KendaraanController::class, 'edit'])->name('kendaraan.edit');
        Route::get('/kendaraan/{id}', [KendaraanController::class, 'show'])->name('kendaraan.show');
        Route::put('/kendaraan/{id}', [KendaraanController::class, 'update'])->name('kendaraan.update');
        Route::delete('/kendaraan/{id}', [KendaraanController::class, 'destroy'])->name('kendaraan.destroy');

        // ===== KELOLA AULA (MASTER DATA) =====
        Route::post('/aula/{id}/toggle-status', [AulaController::class, 'toggleStatus'])->name('aula.toggle-status');
        Route::post('/aula/{id}/delete-photos', [AulaController::class, 'deletePhotos'])->name('aula.delete-photos');
        Route::delete('/aula/{id}/delete-photo', [AulaController::class, 'deletePhoto'])->name('aula.delete-photo');
        Route::resource('aula', AulaController::class);

        // ===== KELOLA AKUN =====
        Route::get('/kelolaakun', [UserController::class, 'index'])->name('kelolaakun.index');
        Route::get('/kelolaakun/create', [UserController::class, 'create'])->name('kelolaakun.create');
        Route::post('/kelolaakun', [UserController::class, 'store'])->name('kelolaakun.store');
        Route::get('/kelolaakun/{id}/edit', [UserController::class, 'edit'])->name('kelolaakun.edit');
        Route::put('/kelolaakun/{id}', [UserController::class, 'update'])->name('kelolaakun.update');
        Route::delete('/kelolaakun/{id}', [UserController::class, 'destroy'])->name('kelolaakun.destroy');
    });


// 2. RUTE PENGELOLA SURAT (Admin & Pengelola Surat)
Route::middleware(['auth', 'cek.status', 'role:admin,pengelola_surat'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ===== KELOLA SURAT =====
        Route::get('/kelolasurat', [AdminSuratController::class, 'index'])->name('kelolasurat.index');
        Route::get('/kelolasurat/export', [AdminSuratController::class, 'export'])->name('kelolasurat.export');
        Route::get('/kelolasurat/pengaturan', [AdminSuratController::class, 'pengaturan'])->name('kelolasurat.pengaturan');
        Route::put('/kelolasurat/pengaturan', [AdminSuratController::class, 'updatePengaturan'])->name('kelolasurat.pengaturan.update');
        Route::post('/kelolasurat/bulk-destroy', [AdminSuratController::class, 'bulkDestroy'])->name('kelolasurat.bulk-destroy');
        Route::get('/kelolasurat/{id}/download', [AdminSuratController::class, 'downloadFile'])->name('kelolasurat.download-file');
        Route::get('/kelolasurat/{id}/edit', [AdminSuratController::class, 'edit'])->name('kelolasurat.edit');
        Route::put('/kelolasurat/{id}', [AdminSuratController::class, 'update'])->name('kelolasurat.update');
        Route::delete('/kelolasurat/{id}', [AdminSuratController::class, 'destroy'])->name('kelolasurat.destroy');
    });


// 3. RUTE PENGELOLA AULA / BOOKING (Admin & Pengelola Aula)
Route::middleware(['auth', 'cek.status', 'role:admin,pengelola_aula'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ===== KELOLA BOOKING =====
        Route::get('/kelolabooking', [AdminBookingController::class, 'index'])->name('kelolabooking.index');
        Route::get('/kelolabooking/export', [AdminBookingController::class, 'export'])->name('kelolabooking.export');
        Route::post('/kelolabooking/bulk-destroy', [AdminBookingController::class, 'bulkDestroy'])->name('kelolabooking.bulk-destroy');
        Route::get('/kelolabooking/{id}', [AdminBookingController::class, 'detail'])->name('kelolabooking.detail');
        Route::post('/kelolabooking/{id}/approve', [AdminBookingController::class, 'approve'])->name('kelolabooking.approve');
        Route::post('/kelolabooking/{id}/reject', [AdminBookingController::class, 'reject'])->name('kelolabooking.reject');
        Route::post('/kelolabooking/{id}/complete', [AdminBookingController::class, 'complete'])->name('kelolabooking.complete');
        Route::put('/kelolabooking/{id}', [AdminBookingController::class, 'update'])->name('kelolabooking.update');
        Route::delete('/kelolabooking/{id}', [AdminBookingController::class, 'destroy'])->name('kelolabooking.destroy');
    });


// 4. RUTE PENGELOLA ABSENSI (Admin & Pengelola Absensi)
Route::middleware(['auth', 'cek.status', 'role:admin,pengelola_absensi'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ===== KELOLA PEGAWAI =====
        Route::get('/pegawai', [AbsensiController::class, 'pegawai'])->name('pegawai.index');

        // ===== KELOLA ABSENSI =====
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('/absensi/create', [AbsensiController::class, 'create'])->name('absensi.create');
        Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');
        Route::post('/absensi/bulk-destroy', [AbsensiController::class, 'bulkDestroy'])->name('absensi.bulk-destroy');
        Route::get('/absensi/export-rekap', [AbsensiController::class, 'exportRekap'])->name('absensi.export-rekap');
        Route::get('/absensi/{id}/qr', [AbsensiController::class, 'qrCode'])->name('absensi.qr');
        Route::post('/absensi/{id}/qr/regenerate', [AbsensiController::class, 'regenerateQr'])->name('absensi.qr-regenerate');
        Route::get('/absensi/{id}/export', [AbsensiController::class, 'exportSesi'])->name('absensi.export');
        Route::get('/absensi/{id}', [AbsensiController::class, 'show'])->name('absensi.show');
        Route::post('/absensi/{id}/kehadiran', [AbsensiController::class, 'updateKehadiran'])->name('absensi.update-kehadiran');
        Route::post('/absensi/{id}/lock', [AbsensiController::class, 'lock'])->name('absensi.lock');
        Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');
    });