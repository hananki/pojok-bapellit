<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiRekapExport;
use App\Exports\AbsensiSesiExport;
use App\Http\Controllers\Controller;
use App\Models\AbsensiDetail;
use App\Models\AbsensiSesi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use SimpleSoftwareIO\QrCode\Generator;

class AbsensiController extends Controller
{
    /**
     * Daftar semua sesi absensi
     */
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'hari_ini');
        $search = $request->input('search');

        $query = AbsensiSesi::with('creator')
            ->withCount([
                'details as jumlah_hadir' => fn ($q) => $q->where('status_kehadiran', 'hadir'),
                'details as jumlah_tidak' => fn ($q) => $q->where('status_kehadiran', 'tidak'),
                'details as details_count',
            ]);

        // Filter: hanya 'hari_ini' dan 'semua'
        if ($filter === 'hari_ini') {
            $query->whereDate('tanggal', today());
        }

        // Search by nama sesi
        if ($search) {
            $query->where('nama_sesi', 'like', "%{$search}%");
        }

        // Urutkan: tanggal terbaru → created_at → id
        $sesiList = $query->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        // Preview sesi dalam rentang rekap (kalau tanggal diisi)
        $sesiRekap = collect();
        if ($request->filled('tanggal_dari') && $request->filled('tanggal_ke')) {
            $sesiRekap = AbsensiSesi::with('details')
                ->whereBetween('tanggal', [
                    Carbon::parse($request->tanggal_dari)->startOfDay(),
                    Carbon::parse($request->tanggal_ke)->endOfDay(),
                ])
                ->orderByDesc('tanggal')
                ->limit(50)
                ->get();
        }

        return view('admin.absensi.index', compact(
            'sesiList',
            'filter',
            'search',
            'sesiRekap'
        ));
    }

    /**
     * Form buat sesi baru
     */
    public function create()
    {
        // Bidang unik
        $bidangList = User::where('status', 'aktif')
            ->whereNotNull('bidang')
            ->where('bidang', '!=', '')
            ->distinct()
            ->orderBy('bidang')
            ->pluck('bidang')
            ->values()
            ->toArray();

        $adaTanpaBidang = User::where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNull('bidang')->orWhere('bidang', '');
            })
            ->exists();

        if ($adaTanpaBidang) {
            array_unshift($bidangList, '(Tanpa Bidang)');
        }

        // Jabatan unik
        $jabatanList = User::where('status', 'aktif')
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->distinct()
            ->orderBy('jabatan')
            ->pluck('jabatan')
            ->values()
            ->toArray();

        $adaTanpaJabatan = User::where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNull('jabatan')->orWhere('jabatan', '');
            })
            ->exists();

        if ($adaTanpaJabatan) {
            array_unshift($jabatanList, '(Tanpa Jabatan)');
        }

        // Semua user aktif (untuk pencarian nama) — normalize label
        $userList = User::where('status', 'aktif')
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan', 'bidang'])
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'jabatan' => $u->jabatan ?: '(Tanpa Jabatan)',
                    'bidang' => $u->bidang ?: '(Tanpa Bidang)',
                ];
            })
            ->toArray();

        return view('admin.absensi.create', compact('bidangList', 'jabatanList', 'userList'));
    }

    /**
     * Simpan sesi baru + generate detail absensi untuk user yang DIPILIH
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_sesi' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'pilih_bidang' => 'nullable|array',
            'pilih_bidang.*' => 'string|max:255',
            'pilih_jabatan' => 'nullable|array',
            'pilih_jabatan.*' => 'string|max:255',
            'pilih_user_ids' => 'nullable|array',
            'pilih_user_ids.*' => 'integer|exists:users,id',
            'exclude_user_ids' => 'nullable|array',
            'exclude_user_ids.*' => 'integer|exists:users,id',
        ]);

        $sesi = AbsensiSesi::create([
            'nama_sesi' => $validated['nama_sesi'],
            'tanggal' => $validated['tanggal'],
            'lokasi' => $validated['lokasi'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
            'is_default' => false,
            'is_locked' => false,
            'created_by' => Auth::id(),
            'token_qr' => Str::random(48),
            'token_generated_at' => now(),
            'qr_lifetime_seconds' => 0,
            'qr_auto_refresh' => false,
        ]);

        // ============================================
        // Filter INCLUDE (whitelist)
        // ============================================
        $pilihBidang = $validated['pilih_bidang'] ?? [];
        $pilihJabatan = $validated['pilih_jabatan'] ?? [];
        $pilihUserIds = $validated['pilih_user_ids'] ?? [];
        $excludeUserIds = $validated['exclude_user_ids'] ?? [];

        $hasFilter = ! empty($pilihBidang) || ! empty($pilihJabatan) || ! empty($pilihUserIds);

        $users = collect();

        if ($hasFilter) {
            $query = User::where('status', 'aktif')
                ->where(function ($q) use ($pilihBidang, $pilihJabatan, $pilihUserIds) {

                    // ===== Bidang =====
                    if (! empty($pilihBidang)) {
                        $bidangNormal = [];
                        $includeTanpaBidang = false;

                        foreach ($pilihBidang as $b) {
                            if ($b === '(Tanpa Bidang)') {
                                $includeTanpaBidang = true;
                            } else {
                                $bidangNormal[] = $b;
                            }
                        }

                        $q->orWhere(function ($sub) use ($bidangNormal, $includeTanpaBidang) {
                            if (! empty($bidangNormal)) {
                                $sub->whereIn('bidang', $bidangNormal);
                            }
                            if ($includeTanpaBidang) {
                                $sub->orWhere(function ($q2) {
                                    $q2->whereNull('bidang')->orWhere('bidang', '');
                                });
                            }
                        });
                    }

                    // ===== Jabatan =====
                    if (! empty($pilihJabatan)) {
                        $jabatanNormal = [];
                        $includeTanpaJabatan = false;

                        foreach ($pilihJabatan as $j) {
                            if ($j === '(Tanpa Jabatan)') {
                                $includeTanpaJabatan = true;
                            } else {
                                $jabatanNormal[] = $j;
                            }
                        }

                        $q->orWhere(function ($sub) use ($jabatanNormal, $includeTanpaJabatan) {
                            if (! empty($jabatanNormal)) {
                                $sub->whereIn('jabatan', $jabatanNormal);
                            }
                            if ($includeTanpaJabatan) {
                                $sub->orWhere(function ($q2) {
                                    $q2->whereNull('jabatan')->orWhere('jabatan', '');
                                });
                            }
                        });
                    }

                    // ===== User spesifik (include) =====
                    if (! empty($pilihUserIds)) {
                        $q->orWhereIn('id', $pilihUserIds);
                    }
                });

            // EXCLUDE: kurangi user yang di-blacklist
            if (! empty($excludeUserIds)) {
                $query->whereNotIn('id', $excludeUserIds);
            }

            $users = $query->select('id')->get();
        }

        // Bulk insert peserta
        if ($users->isNotEmpty()) {
            $data = $users->map(fn ($u) => [
                'absensi_sesi_id' => $sesi->id,
                'user_id' => $u->id,
                'status_kehadiran' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            AbsensiDetail::insert($data);
        }

        // Info pesan sukses
        $infoPilih = [];
        if (! empty($pilihBidang)) {
            $infoPilih[] = count($pilihBidang).' bidang';
        }
        if (! empty($pilihJabatan)) {
            $infoPilih[] = count($pilihJabatan).' jabatan';
        }
        if (! empty($pilihUserIds)) {
            $infoPilih[] = count($pilihUserIds).' user spesifik';
        }

        $pesan = "Sesi \"{$sesi->nama_sesi}\" berhasil dibuat dengan {$users->count()} peserta.";
        if (! empty($infoPilih)) {
            $pesan .= ' (Dari: '.implode(', ', $infoPilih).')';
        } else {
            $pesan .= ' (Tidak ada peserta yang dipilih)';
        }
        if (! empty($excludeUserIds)) {
            $pesan .= ' — '.count($excludeUserIds).' user dikecualikan.';
        }

        return redirect()
            ->route('admin.absensi.show', $sesi->id)
            ->with('success', $pesan);
    }

    /**
     * Halaman absensi 1 sesi
     */
    public function show(Request $request, int $id)
    {
        $sesi = AbsensiSesi::with('creator')->findOrFail($id);
        $search = $request->input('search');

        // PERBAIKAN: Gabungkan query agar konsisten & tidak ada duplikasi logika
        $peserta = AbsensiDetail::with('user:id,name,bidang,status')
            ->where('absensi_sesi_id', $id)
            ->when($search, function ($q, $search) {
                return $q->whereHas('user', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            })
            ->whereHas('user')
            ->get()
            ->map(function ($detail) {
                return (object) [
                    'id' => $detail->user->id,
                    'name' => $detail->user->name,
                    'bidang' => $detail->user->bidang,
                    'status' => $detail->user->status,
                    'detail_id' => $detail->id,
                    'status_kehadiran' => $detail->status_kehadiran,
                    'keterangan' => $detail->keterangan,
                    'waktu_absen' => $detail->waktu_absen,
                ];
            })
            ->sortBy('name')
            ->values();

        // Generate token QR kalau belum ada & sesi belum dikunci
        if (! $sesi->is_locked && empty($sesi->token_qr)) {
            $sesi->token_qr = Str::random(48);
            $sesi->token_generated_at = now();
            $sesi->save();
        }

        $scanUrl = null;
        $qrCode = null;

        if ($sesi->token_qr && ! $sesi->is_locked) {
            $scanUrl = route('absensi.scan', ['token' => $sesi->token_qr]);

            try {
                /** @var Generator $qrGenerator */
                $qrGenerator = QrCode::getFacadeRoot();
                $qrCode = $qrGenerator->size(300)->margin(2)->generate($scanUrl);
            } catch (\Exception $e) {
                $qrCode = null;
            }
        }

        return view('admin.absensi.show', compact(
            'sesi',
            'peserta',
            'search',
            'scanUrl',
            'qrCode'
        ));
    }

    /**
     * Update kehadiran peserta
     */
    public function updateKehadiran(Request $request, int $id)
    {
        $sesi = AbsensiSesi::findOrFail($id);

        if ($sesi->is_locked) {
            return redirect()
                ->route('admin.absensi.show', $id)
                ->with('error', 'Absensi sudah dikunci, tidak bisa diubah!');
        }

        $request->validate([
            'kehadiran' => 'required|array',
            'kehadiran.*' => 'nullable|in:hadir,tidak',
            'keterangan' => 'nullable|array',
            'waktu_absen' => 'nullable|array',
            'waktu_absen.*' => 'nullable|date_format:H:i',
        ]);

        $totalDiupdate = 0;
        foreach ($request->kehadiran as $userId => $status) {
            if (! $status) {
                continue;
            }

            $keterangan = $status === 'tidak'
                ? ($request->keterangan[$userId] ?? null)
                : null;

            $waktuAbsen = $status === 'hadir'
                ? ($request->waktu_absen[$userId] ?? now()->format('H:i'))
                : null;

            AbsensiDetail::updateOrCreate(
                [
                    'absensi_sesi_id' => $id,
                    'user_id' => $userId,
                ],
                [
                    'status_kehadiran' => $status,
                    'keterangan' => $keterangan,
                    'waktu_absen' => $waktuAbsen,
                ]
            );

            $totalDiupdate++;
        }

        return redirect()
            ->route('admin.absensi.show', $id)
            ->with('success', "Absensi berhasil disimpan! {$totalDiupdate} peserta telah diabsen.");
    }

    /**
     * Kunci absensi
     */
    public function lock(int $id)
    {
        $sesi = AbsensiSesi::findOrFail($id);

        if ($sesi->is_locked) {
            return redirect()
                ->route('admin.absensi.show', $id)
                ->with('error', 'Absensi sudah terkunci sebelumnya.');
        }

        $sesi->update([
            'is_locked' => true,
            'token_qr' => null,
            'token_generated_at' => null,
        ]);

        return redirect()
            ->route('admin.absensi.show', $id)
            ->with('success', "Absensi \"{$sesi->nama_sesi}\" berhasil dikunci. QR Code sudah tidak berlaku.");
    }

    /**
     * Regenerate token manual
     */
    public function regenerateQr(int $id)
    {
        $sesi = AbsensiSesi::findOrFail($id);

        if ($sesi->is_locked) {
            return redirect()
                ->route('admin.absensi.show', $id)
                ->with('error', 'Sesi sudah dikunci. QR tidak bisa diperbarui.');
        }

        $sesi->token_qr = Str::random(48);
        $sesi->token_generated_at = now();
        $sesi->save();

        return redirect()
            ->route('admin.absensi.show', $id)
            ->with('success', 'QR Code berhasil diperbarui!');
    }

    // ============================================================
    // EXPORT METHODS
    // ============================================================

    /**
     * Export Excel per sesi
     */
    public function exportSesi(int $id)
    {
        $sesi = AbsensiSesi::findOrFail($id);

        $tanggal = Carbon::parse($sesi->tanggal)->format('Y-m-d');
        $namaSesi = Str::slug($sesi->nama_sesi, '_');
        $namaFile = "Daftar_Hadir_{$namaSesi}_{$tanggal}.xlsx";

        return Excel::download(new AbsensiSesiExport($sesi), $namaFile);
    }

    /**
     * Export Rekap Excel berdasarkan rentang tanggal
     */
    public function exportRekap(Request $request)
    {
        $request->validate([
            'tanggal_dari' => 'required|date',
            'tanggal_ke' => 'required|date|after_or_equal:tanggal_dari',
            'ids' => 'nullable|array',
            'ids.*' => 'integer|exists:absensi_sesi,id',
        ], [
            'tanggal_dari.required' => 'Tanggal "dari" wajib diisi.',
            'tanggal_ke.required' => 'Tanggal "ke" wajib diisi.',
            'tanggal_ke.after_or_equal' => 'Tanggal "ke" harus sama atau setelah tanggal "dari".',
            'ids.*.exists' => 'Terdapat sesi yang tidak valid atau sudah dihapus.',
        ]);

        $tanggalDari = Carbon::parse($request->tanggal_dari)->startOfDay();
        $tanggalKe = Carbon::parse($request->tanggal_ke)->endOfDay();

        $includeIds = $request->input('ids', []);

        if (empty($includeIds)) {
            return redirect()
                ->route('admin.absensi.index')
                ->with('error', 'Tidak ada sesi yang dipilih untuk di-export. Silakan reset filter atau pilih ulang sesi.');
        }

        $sesiList = AbsensiSesi::with('details')
            ->whereBetween('tanggal', [$tanggalDari, $tanggalKe])
            ->whereIn('id', $includeIds)
            ->orderBy('tanggal')
            ->get();

        if ($sesiList->isEmpty()) {
            return redirect()
                ->route('admin.absensi.index')
                ->with('error', 'Data sesi yang Anda pilih tidak ditemukan di rentang tanggal tersebut.');
        }

        $labelDari = $tanggalDari->format('Y-m-d');
        $labelKe = $tanggalKe->format('Y-m-d');
        $namaFile = "Rekap_Absensi_{$labelDari}_sd_{$labelKe}.xlsx";

        return Excel::download(
            new AbsensiRekapExport($sesiList, $tanggalDari, $tanggalKe, []),
            $namaFile
        );
    }

    /**
     * Hapus sesi absensi (Individual)
     */
    public function destroy(int $id)
    {
        $sesi = AbsensiSesi::findOrFail($id);
        $namaSesi = $sesi->nama_sesi;
        $sesi->delete();

        return redirect()
            ->route('admin.absensi.index')
            ->with('success', "Sesi \"{$namaSesi}\" berhasil dihapus.");
    }

    /**
     * Hapus banyak sesi sekaligus (Bulk Delete)
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:absensi_sesi,id',
        ]);

        $ids = $request->input('ids');

        AbsensiSesi::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($ids).' sesi berhasil dihapus.',
        ]);
    }

    /**
     * Kelola Pegawai
     */
    public function pegawai(Request $request)
    {
        $search = $request->input('search');
        $filterBidang = $request->input('bidang');

        $pegawai = User::where('status', 'aktif')
            ->withCount('absensiDetails')
            ->when($search, function ($q, $search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filterBidang, function ($q, $bidang) {
                return $q->where('bidang', $bidang);
            })
            ->orderBy('name')
            ->paginate(100)
            ->withQueryString();

        $bidangList = User::where('status', 'aktif')
            ->whereNotNull('bidang')
            ->distinct()
            ->orderBy('bidang')
            ->pluck('bidang');

        return view('admin.absensi.pegawai', compact('pegawai', 'search', 'filterBidang', 'bidangList'));
    }

    /**
     * Khusus dipanggil oleh Chatbot AI (Function Calling)
     * Menghindari redirect agar AI bisa membaca hasilnya sebagai teks.
     */
       public function storeViaChatbot(Request $request)
    {
        $validated = $request->validate([
            'nama_sesi' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'lokasi' => 'required|string|max:255',
            'catatan' => 'nullable|string',
            'pilih_bidang' => 'nullable|array',
            'pilih_bidang.*' => 'string',
            'pilih_jabatan' => 'nullable|array',
            'pilih_jabatan.*' => 'string',
            'pilih_user_ids' => 'nullable|array',
            'pilih_user_ids.*' => 'integer|exists:users,id',
        ]);

        $sesi = AbsensiSesi::create([
            'nama_sesi' => $validated['nama_sesi'],
            'tanggal' => $validated['tanggal'],
            'lokasi' => $validated['lokasi'],
            'catatan' => $validated['catatan'] ?? null,
            'is_default' => false,
            'is_locked' => false,
            'created_by' => Auth::id(),
            'token_qr' => Str::random(48),
            'token_generated_at' => now(),
            'qr_lifetime_seconds' => 0,
            'qr_auto_refresh' => false,
        ]);

        $pilihBidang = $validated['pilih_bidang'] ?? [];
        $pilihJabatan = $validated['pilih_jabatan'] ?? [];
        $pilihUserIds = $validated['pilih_user_ids'] ?? [];

        $hasFilter = ! empty($pilihBidang) || ! empty($pilihJabatan) || ! empty($pilihUserIds);

        $users = collect();

        if ($hasFilter) {
            $query = User::where('status', 'aktif')
                ->where(function ($q) use ($pilihBidang, $pilihJabatan, $pilihUserIds) {
                    if (! empty($pilihBidang)) {
                        $bidangNormal = [];
                        $includeTanpaBidang = false;
                        foreach ($pilihBidang as $b) {
                            if (strtoupper(trim($b)) === 'TANPA BIDANG' || strtoupper(trim($b)) === '(TANPA BIDANG)') {
                                $includeTanpaBidang = true;
                            } else {
                                $bidangNormal[] = strtoupper(trim($b));
                            }
                        }
                        $q->orWhere(function ($sub) use ($bidangNormal, $includeTanpaBidang) {
                            if (! empty($bidangNormal)) {
                                $sub->whereIn('bidang', $bidangNormal);
                            }
                            if ($includeTanpaBidang) {
                                $sub->orWhere(function ($q2) {
                                    $q2->whereNull('bidang')->orWhere('bidang', '');
                                });
                            }
                        });
                    }

                    if (! empty($pilihJabatan)) {
                        $jabatanNormal = [];
                        $includeTanpaJabatan = false;
                        foreach ($pilihJabatan as $j) {
                            if (strtoupper(trim($j)) === 'TANPA JABATAN' || strtoupper(trim($j)) === '(TANPA JABATAN)') {
                                $includeTanpaJabatan = true;
                            } else {
                                $jabatanNormal[] = strtoupper(trim($j));
                            }
                        }
                        $q->orWhere(function ($sub) use ($jabatanNormal, $includeTanpaJabatan) {
                            if (! empty($jabatanNormal)) {
                                $sub->whereIn('jabatan', $jabatanNormal);
                            }
                            if ($includeTanpaJabatan) {
                                $sub->orWhere(function ($q2) {
                                    $q2->whereNull('jabatan')->orWhere('jabatan', '');
                                });
                            }
                        });
                    }

                    if (! empty($pilihUserIds)) {
                        $q->orWhereIn('id', $pilihUserIds);
                    }
                });

            $users = $query->select('id')->get();
        } else {
            $users = User::where('status', 'aktif')->get();
        }

        if ($users->isNotEmpty()) {
            $data = $users->map(fn ($u) => [
                'absensi_sesi_id' => $sesi->id,
                'user_id' => $u->id,
                'status_kehadiran' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();
            AbsensiDetail::insert($data);
        }

        return response()->json([
            'success' => true,
            'message' => "Sesi \"{$sesi->nama_sesi}\" berhasil dibuat untuk tanggal ".Carbon::parse($sesi->tanggal)->format('d-m-Y')." di {$sesi->lokasi} dengan {$users->count()} peserta.",
        ]);
    }
}