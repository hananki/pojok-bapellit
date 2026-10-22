<?php

namespace App\Http\Controllers;

use App\Events\SuratSubmitted;
use App\Exceptions\NomorSuratConflictException;
use App\Http\Requests\StoreSuratRequest;
use App\Http\Requests\UpdateSuratRequest;
use App\Models\PengaturanSurat;
use App\Models\Surat;
use App\Services\FileStorageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SuratController extends Controller
{
    /**
     * Halaman utama: Form ambil surat + daftar surat dengan filter
     */
    public function index(Request $request)
    {
        // Default filter: hanya surat milik user login
        $filter = $request->input('filter', 'mine');

        // Eager load relasi user untuk menampilkan nama/bidang/foto di riwayat
        $query = Surat::with('user:id,name,bidang,profile_photo');

        if ($filter === 'all') {
            // Tampilkan semua surat (agar nomor surat terlihat jelas urutannya)
            // Urutkan berdasarkan nomor surat descending (terbaru di atas)
            $query->orderBy('no_surat', 'desc');
        } else {
            // Hanya surat milik user yang sedang login
            $query->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc');
        }

        // Fitur pencarian global
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_surat', 'like', "%{$search}%")
                    ->orWhere('isi_surat', 'like', "%{$search}%")
                    ->orWhere('jenis_surat', 'like', "%{$search}%")
                  // Cari juga berdasarkan nama user jika filter 'all'
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $surats = $query->paginate(15);

        $pengaturan = PengaturanSurat::getSetting();
        // Generate nomor dummy untuk tampilan form (tidak increment sampai submit)
        $nomorTersedia = $pengaturan->generateNomor(1);

        return view('surat.index', compact('surats', 'nomorTersedia', 'filter'));
    }

    /**
     * Simpan pengajuan baru + assign nomor + increment counter dengan concurrency lock
     */
    public function store(StoreSuratRequest $request)
    {
        $uploadedFilePath = null;
        $nomorSurat = null;

        try {
            $surat = DB::transaction(function () use ($request, &$uploadedFilePath, &$nomorSurat) {
                // Lock baris pengaturan_surats secara eksklusif (FOR UPDATE)
                // Memastikan antrean request concurrent sehingga tidak ada 2 user mengambil nomor yang sama
                $pengaturan = PengaturanSurat::where('id', 1)->lockForUpdate()->first();
                if (! $pengaturan) {
                    $pengaturan = PengaturanSurat::getSetting();
                    $pengaturan = PengaturanSurat::where('id', $pengaturan->id)->lockForUpdate()->first();
                }

                $nomorSekarang = $pengaturan->generateNomor(1);
                $expectedNomor = trim((string) $request->input('no_surat_hidden'));

                // Jika nomor yang ditampilkan di layar user sudah berbeda dengan nomor sah berikutnya
                // (artinya ada user lain yang sudah mengklik simpan lebih dulu)
                if (! empty($expectedNomor) && $expectedNomor !== $nomorSekarang) {
                    throw new NomorSuratConflictException(
                        "Nomor surat '{$expectedNomor}' sudah diambil oleh pengguna lain yang menyimpan lebih dulu. Silakan muat ulang halaman untuk mendapatkan nomor baru."
                    );
                }

                // Cek apakah nomor tersebut sudah pernah tercatat di database
                if (Surat::where('no_surat', $nomorSekarang)->exists()) {
                    throw new NomorSuratConflictException(
                        "Nomor surat '{$nomorSekarang}' sudah terdaftar dalam sistem. Silakan muat ulang halaman untuk mendapatkan nomor berikutnya."
                    );
                }

                // ==========================================
                // PENANGANAN FILE (OPSIONAL)
                // ==========================================
                $fileData = null;
                if ($request->hasFile('file_surat')) {
                    // Upload file dokumen setelah validasi nomor aman
                    $fileData = FileStorageService::storeDocument($request->file('file_surat'), 'uploads/surat');
                    $uploadedFilePath = $fileData['path'];
                }

                $surat = Surat::create([
                    'user_id' => Auth::id(),
                    'no_surat' => $nomorSekarang,
                    'jenis_surat' => $request->input('jenis_surat'),
                    'sifat_surat' => $request->input('sifat_surat'),
                    'tanggal' => $request->input('tanggal', now()),
                    'no_indek' => $request->input('no_indek'),
                    'alamat_tujuan' => $request->input('alamat_tujuan'),
                    'isi_surat' => $request->input('isi_surat'),
                    'keterangan' => $request->input('keterangan'),
                    'file_surat' => $fileData ? $fileData['path'] : null,
                    'file_surat_original_name' => $fileData ? $fileData['original_name'] : null,
                ]);

                // Increment counter HANYA setelah record surat berhasil dibuat
                $pengaturan->incrementCounter(1);

                $nomorSurat = $nomorSekarang;

                return $surat;
            });

            broadcast(new SuratSubmitted($surat));

            return redirect()->route('surat.index')
                ->with('success', 'Surat berhasil diambil! Nomor surat Anda: '.$nomorSurat);

        } catch (NomorSuratConflictException $e) {
            if ($uploadedFilePath && Storage::disk('public')->exists($uploadedFilePath)) {
                Storage::disk('public')->delete($uploadedFilePath);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();

        } catch (QueryException $e) {
            if ($uploadedFilePath && Storage::disk('public')->exists($uploadedFilePath)) {
                Storage::disk('public')->delete($uploadedFilePath);
            }

            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                return redirect()->back()
                    ->with('error', 'Nomor surat sudah diambil oleh pengguna lain yang menyimpan pada saat bersamaan. Silakan coba kembali untuk mendapatkan nomor terbaru.')
                    ->withInput();
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan basis data: '.$e->getMessage())
                ->withInput();

        } catch (\Exception $e) {
            if ($uploadedFilePath && Storage::disk('public')->exists($uploadedFilePath)) {
                Storage::disk('public')->delete($uploadedFilePath);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update data surat (Edit)
     */
    public function update(UpdateSuratRequest $request, int $id)
    {
        $surat = Surat::where('user_id', Auth::id())->findOrFail($id);

        try {
            $data = $request->only([
                'jenis_surat', 'sifat_surat', 'tanggal', 'no_indek',
                'alamat_tujuan', 'isi_surat', 'keterangan',
            ]);

            // Handle Upload File Baru (jika ada)
            if ($request->hasFile('file_surat')) {
                // Hapus file lama dari storage
                if ($surat->file_surat && Storage::disk('public')->exists($surat->file_surat)) {
                    Storage::disk('public')->delete($surat->file_surat);
                }

                $fileData = FileStorageService::storeDocument($request->file('file_surat'), 'uploads/surat');
                $data['file_surat'] = $fileData['path'];
                $data['file_surat_original_name'] = $fileData['original_name'];
            }

            $surat->update($data);

            return redirect()->route('surat.index')
                ->with('success', 'Data surat berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate surat: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Download / Preview file surat
     */
    public function download(int $id)
    {
        // Izinkan download untuk semua user yang login
        $surat = Surat::findOrFail($id);

        if (! $surat->file_surat || ! Storage::disk('public')->exists($surat->file_surat)) {
            return redirect()->back()->with('error', 'File tidak tersedia.');
        }

        $path = Storage::disk('public')->path($surat->file_surat);

        if (! is_readable($path)) {
            return redirect()->back()->with('error', 'File tidak dapat diakses.');
        }

        $filename = $surat->file_surat_original_name;

        if (empty($filename)) {
            $ext = strtolower(pathinfo($surat->file_surat, PATHINFO_EXTENSION));
            $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $surat->no_surat ?? 'surat').'.'.$ext;
        } else {
            $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $filename);
        }

        $mimeType = FileStorageService::getMimeType($path);
        $disposition = ($mimeType === 'application/pdf') ? 'inline' : 'attachment';

        return response()->streamDownload(function () use ($path) {
            readfile($path);
        }, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * MIME type fallback (deprecated - use FileStorageService::getMimeType instead)
     */
    private function getMimeType(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }

    /**
     * Preview nomor berikutnya (AJAX)
     */
    public function previewNomor()
    {
        $pengaturan = PengaturanSurat::getSetting();
        $nomor = $pengaturan->generateNomor(1);

        return response()->json([
            'success' => true,
            'nomor' => $nomor,
        ]);
    }
}