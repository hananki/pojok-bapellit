<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiDetail;
use App\Models\AbsensiSesi;
use App\Models\Aula;
use App\Models\Booking;
use App\Models\User;
use App\Models\Kendaraan;
use App\Models\PajakKendaraan;
use App\Models\Surat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getStatusBadgeClasses(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function index()
    {
        $role = Auth::user()->role;

        // ==========================================
        // 1. DASHBOARD PENGELOLA ABSENSI
        // ==========================================
        if ($role === 'pengelola_absensi') {
            $totalAbsensiSesi = AbsensiSesi::count();
            $hadir = AbsensiDetail::where('status_kehadiran', 'hadir')->count();
            $tidak = AbsensiDetail::where('status_kehadiran', 'tidak')->count();
            $belumAbsen = AbsensiDetail::whereNull('status_kehadiran')->count();

            // Data untuk Grafik 7 Hari Terakhir
            $trenKehadiran = [];
            $trenTidakHadir = [];
            $labelTanggal = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labelTanggal[] = $date->format('d M');
                
                $trenKehadiran[] = AbsensiDetail::whereDate('created_at', $date->format('Y-m-d'))
                    ->where('status_kehadiran', 'hadir')->count();
                    
                $trenTidakHadir[] = AbsensiDetail::whereDate('created_at', $date->format('Y-m-d'))
                    ->where('status_kehadiran', 'tidak')->count();
            }

            // AMBIL 5 PEGAWAI YANG BELUM ABSEN HARI INI
            // Logika: Pegawai aktif yang TIDAK punya data absensi dengan status 'hadir' hari ini
            $pegawaiBelumAbsen = User::where('status', 'aktif')
                ->where(function ($q) {
                    // Hanya cek pegawai biasa (bukan admin)
                    $q->where('is_admin', false)->where('role', '!=', 'admin');
                })
                ->whereDoesntHave('absensiDetails', function($q) {
                    // Cari yang tidak punya record 'hadir' hari ini
                    $q->whereDate('created_at', now()->toDateString())
                      ->where('status_kehadiran', 'hadir');
                })
                ->limit(5)
                ->get();

            return view('admin.dashboard.absensi', compact(
                'totalAbsensiSesi', 'hadir', 'tidak', 'belumAbsen',
                'trenKehadiran', 'trenTidakHadir', 'labelTanggal', 'pegawaiBelumAbsen'
            ));
        }

        // ==========================================
        // 2. DASHBOARD PENGELOLA AULA
        // ==========================================
        if ($role === 'pengelola_aula') {
            $totalBookings = Booking::count();
            $pendingBookings = Booking::where('status', 'pending')->count();
            $approvedBookings = Booking::where('status', 'approved')->count();
            $completedBookings = Booking::where('status', 'completed')->count();

            // 5 Aula Paling Sering Dipakai
            $topAula = Aula::withCount('bookings')
                ->orderBy('bookings_count', 'desc')
                ->limit(5)
                ->get();

            // 5 Booking Terbaru
            $recentBookings = Booking::with(['user', 'aula'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('admin.dashboard.aula', compact(
                'totalBookings', 'pendingBookings', 'approvedBookings', 'completedBookings', 
                'topAula', 'recentBookings'
            ));
        }

        // ==========================================
        // 3. DASHBOARD PENGELOLA SURAT
        // ==========================================
        if ($role === 'pengelola_surat') {
            $suratHariIni = Surat::whereDate('created_at', now()->toDateString())->count();
            $suratMingguIni = Surat::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $suratBulanIni = Surat::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $suratTahunIni = Surat::whereYear('created_at', now()->year)->count();

            // 5 Surat Terakhir
            $suratTerakhir = Surat::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('admin.dashboard.surat', compact(
                'suratHariIni', 'suratMingguIni', 'suratBulanIni', 'suratTahunIni', 'suratTerakhir'
            ));
        }

        // ==========================================
        // 4. DASHBOARD ADMIN (Tampilan Penuh)
        // ==========================================
        
        // ===== STATISTIK PENGGUNA =====
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->orWhere('role', 'admin')->count();
        $activePegawai = User::where('status', 'aktif')->where(function ($q) {
            $q->where('is_admin', false)->where('role', '!=', 'admin');
        })->count();
        $inactiveAccounts = User::where('status', 'nonaktif')->count();

        // ===== STATISTIK BOOKING =====
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $approvedBookings = Booking::where('status', 'approved')->count();
        $rejectedBookings = Booking::where('status', 'rejected')->count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();

        // ===== STATISTIK AULA =====
        $totalAula = Aula::count();
        $activeAula = Aula::where('status_aktif', true)->count();
        $inactiveAula = Aula::where('status_aktif', false)->count();

        // ===== STATISTIK ABSENSI =====
        $totalAbsensiSesi = AbsensiSesi::count();
        $totalAbsensi = AbsensiDetail::count();
        $hadir = AbsensiDetail::where('status_kehadiran', 'hadir')->count();
        $tidak = AbsensiDetail::where('status_kehadiran', 'tidak')->count();
        $belumAbsen = AbsensiDetail::whereNull('status_kehadiran')->count();
        
        $izin = 0;
        $sakit = 0;
        $alpa = 0;

        // ===== STATISTIK BOOKING BULAN INI =====
        $bookingThisMonth = Booking::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ===== BOOKING BERDASARKAN STATUS (untuk chart) =====
        $bookingByStatus = Booking::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // ===== BOOKING TREND - 12 BULAN TERAKHIR =====
        $bookingTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Booking::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $bookingTrend[$date->format('M Y')] = $count;
        }

        // ===== ABSENSI DISTRIBUTION =====
        $absensiDistribution = [
            'Hadir' => $hadir,
            'Tidak Hadir' => $tidak,
            'Belum Diabsen' => $belumAbsen,
        ];

        // ===== USER DISTRIBUTION =====
        $userDistribution = [
            'Admin' => $totalAdmins,
            'Pegawai Aktif' => $activePegawai,
            'Nonaktif' => $inactiveAccounts,
        ];

        // ===== BOOKING TERBARU =====
        $recentBookings = Booking::with(['user', 'aula'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ===== AULA PALING SERING DIBOOK =====
        $topAula = Aula::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();

        // ===== STATISTIK KENDARAAN (TAMBAHAN) =====
        $totalKendaraan = Kendaraan::count();
        $kendaraanAktif = Kendaraan::where('status', 'aktif')->count();
        
        $pajakMendesak = PajakKendaraan::where('status', 'belum_bayar')
            ->where('tanggal_jatuh_tempo', '<=', now()->addDays(7))
            ->count();

        // AMBIL DATA KENDARAAN YANG PAJAKNYA MENDEKATI JATUH TEMPO (MAKSIMAL 5)
        $kendaraanMendesakList = PajakKendaraan::with(['kendaraan.user'])
            ->where('status', 'belum_bayar')
            ->where('tanggal_jatuh_tempo', '>=', now()) 
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->take(5)
            ->get();
        
        $kendaraanDistribution = [
            'Aktif' => $kendaraanAktif,
            'Tidak Dipakai' => Kendaraan::where('status', 'tidak dipakai')->count(),
            'Rusak' => Kendaraan::where('status', 'rusak')->count(),
        ];

        // Add badge class helper to view
        $statusBadgeClasses = function (string $status): string {
            return match ($status) {
                'pending' => 'bg-yellow-100 text-yellow-800',
                'approved' => 'bg-blue-100 text-blue-800',
                'completed' => 'bg-green-100 text-green-800',
                'rejected' => 'bg-red-100 text-red-800',
                'cancelled' => 'bg-gray-100 text-gray-800',
                default => 'bg-gray-100 text-gray-800',
            };
        };

        return view('admin.dashboard.index', compact(
            'totalUsers',
            'totalAdmins',
            'activePegawai',
            'inactiveAccounts',
            'totalBookings',
            'pendingBookings',
            'approvedBookings',
            'rejectedBookings',
            'completedBookings',
            'cancelledBookings',
            'totalAula',
            'activeAula',
            'inactiveAula',
            'totalAbsensiSesi',
            'totalAbsensi',
            'hadir',
            'tidak',
            'belumAbsen',
            'izin',
            'sakit',
            'alpa',
            'bookingThisMonth',
            'bookingByStatus',
            'bookingTrend',
            'absensiDistribution',
            'userDistribution',
            'recentBookings',
            'topAula',
            'totalKendaraan',
            'kendaraanAktif',
            'pajakMendesak',
            'kendaraanMendesakList',
            'kendaraanDistribution',
            'statusBadgeClasses'
        ));
    }
}