<?php

namespace App\Services\Chatbot\Tools;

use App\Http\Controllers\SuratController;
use App\Http\Requests\StoreSuratRequest;
use App\Models\PengaturanSurat;
use App\Models\Surat;
use App\Services\Chatbot\Support\DateParser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Tool AI untuk SURAT (alur booking lewat chat teks, dengan konfirmasi):
 *  1. get_nomor_surat    : cek nomor terakhir + nomor tersedia
 *  2. draft_surat        : user menyebut jenis + tujuan -> simpan draft, AI minta konfirmasi ya/tidak
 *  3. konfirmasi_surat   : booking sungguhan, HANYA bila pesan user berisi "ya"
 *     batal_surat        : buang draft bila user menjawab "tidak"
 *  +  get_riwayat_surat  : 5 riwayat surat terakhir user
 */
class SuratTools implements ToolGroup
{
    private const TOOLS = [
        'get_nomor_surat',
        'draft_surat',
        'konfirmasi_surat',
        'batal_surat',
        'get_riwayat_surat',
    ];

    private const DEFAULT_ISI = '-';
    private const DRAFT_TTL_MINUTES = 15;

    // Kata umum yang tidak dianggap sebagai bukti user menyebut jenis/tujuan surat
    private const STOPWORDS = [
        'surat', 'nomor', 'nomer', 'ambil', 'yang', 'dari', 'untuk', 'dengan',
        'itu', 'ini', 'booking', 'tolong', 'dong', 'bisa', 'mau', 'ingin',
    ];

    public function __construct(
        private DateParser $dates,
        private ToolSupport $support,
    ) {
    }

    public function handles(string $name): bool
    {
        return in_array($name, self::TOOLS, true);
    }

    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_nomor_surat',
                    'description' => 'Cek nomor surat terakhir yang terpakai dan nomor berikutnya yang tersedia. '
                        . 'Gunakan saat user bertanya nomor surat sekarang/terakhir/tersedia. '
                        . 'Ini hanya informasi, belum mengambil nomor.',
                    'parameters' => ['type' => 'object', 'properties' => (object) []],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'draft_surat',
                    'description' => 'Siapkan draft pengambilan nomor surat. '
                        . 'ALUR: jika user ingin mengambil nomor surat tetapi BELUM menyebut jenis surat dan tujuan surat, '
                        . 'JANGAN panggil tool ini. Balas teks biasa: "Silakan sebutkan jenis surat dan tujuan surat-nya ya." '
                        . 'lalu berhenti dan tunggu jawaban user. '
                        . 'Panggil tool ini HANYA setelah user sendiri menyebutkan jenis_surat dan alamat_tujuan. '
                        . 'DILARANG menebak atau mengarang dari contoh/riwayat. '
                        . 'salin_dari_terakhir=true hanya jika user eksplisit berkata "tujuan sama" / "seperti surat terakhir". '
                        . 'Ini BELUM mengambil nomor. Setelah dipanggil, tampilkan ringkasan ke user, '
                        . 'tanyakan "Apakah data ini sudah benar? (ya/tidak)", lalu BERHENTI dan tunggu jawaban user.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'jenis_surat' => ['type' => 'string', 'description' => 'Jenis surat, sesuai ucapan user'],
                            'alamat_tujuan' => ['type' => 'string', 'description' => 'Tujuan surat, sesuai ucapan user'],
                            'salin_dari_terakhir' => [
                                'type' => 'boolean',
                                'description' => 'true jika user minta memakai data surat terakhirnya',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'konfirmasi_surat',
                    'description' => 'Ambil nomor surat sesuai draft. HANYA panggil setelah user menjawab "ya"/setuju '
                        . 'pada pesan terpisah setelah ringkasan draft ditampilkan. '
                        . 'Dilarang dipanggil di giliran yang sama dengan draft_surat.',
                    'parameters' => ['type' => 'object', 'properties' => (object) []],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'batal_surat',
                    'description' => 'Batalkan draft surat bila user menjawab "tidak" atau membatalkan.',
                    'parameters' => ['type' => 'object', 'properties' => (object) []],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_riwayat_surat',
                    'description' => 'Lihat 5 riwayat surat terakhir milik user.',
                    'parameters' => ['type' => 'object', 'properties' => (object) []],
                ],
            ],
        ];
    }

    public function run(string $name, array &$arguments, Authenticatable $user): string
    {
        return match ($name) {
            'get_nomor_surat' => $this->nomorTersedia(),
            'draft_surat' => $this->draft($arguments, $user),
            'konfirmasi_surat' => $this->konfirmasi($user),
            'batal_surat' => $this->batal($user),
            'get_riwayat_surat' => $this->history($user),
            default => "Fungsi tidak dikenal: {$name}",
        };
    }

    public function describe(string $name, array $arguments, string $result): string
    {
        return $result;
    }

    // ------------------------------------------------------------------
    // 1. Cek nomor
    // ------------------------------------------------------------------

    private function nomorTersedia(): string
    {
        $next = PengaturanSurat::getSetting()->generateNomor(1);
        $last = Surat::orderBy('id', 'desc')->first();

        $lastText = $last
            ? "Nomor surat terakhir terpakai: {$last->no_surat} ({$last->jenis_surat})"
            : 'Belum ada surat yang tercatat.';

        return "{$lastText}\n"
            . "Nomor tersedia berikutnya: {$next}\n"
            . '(Informasi saja, nomor belum diambil. Jika user ingin mengambilnya, tanyakan jenis surat dan tujuan surat '
            . 'dengan teks biasa, lalu tunggu jawaban user.)';
    }

    // ------------------------------------------------------------------
    // 2. Draft
    // ------------------------------------------------------------------

    private function draft(array $arguments, Authenticatable $user): string
    {
        $jenis = trim((string) ($arguments['jenis_surat'] ?? ''));
        $tujuan = trim((string) ($arguments['alamat_tujuan'] ?? ''));
        $salin = ! empty($arguments['salin_dari_terakhir']);

        if ($salin) {
            $last = Surat::where('user_id', $user->getAuthIdentifier())
                ->orderBy('id', 'desc')
                ->first();

            if ($last) {
                $jenis = $jenis !== '' ? $jenis : (string) $last->jenis_surat;
                $tujuan = $tujuan !== '' ? $tujuan : (string) $last->alamat_tujuan;
            }
        } else {
            // Guard anti-karangan: minimal salah satu nilai harus muncul di pesan user saat ini
            $pesan = $this->pesanUser();
            if ($pesan !== '' && ! $this->disebutUser($pesan, [$jenis, $tujuan])) {
                return 'Jenis surat dan tujuan surat tidak ditemukan di pesan user, jadi dianggap karangan. '
                    . 'JANGAN mengarang. Balas teks biasa: "Silakan sebutkan jenis surat dan tujuan surat-nya ya." '
                    . 'lalu berhenti dan tunggu jawaban user.';
            }
        }

        $kurang = [];
        if ($jenis === '') {
            $kurang[] = 'jenis surat';
        }
        if ($tujuan === '') {
            $kurang[] = 'tujuan surat';
        }
        if ($kurang) {
            return 'Data belum lengkap. Tanyakan ke user dengan teks biasa: ' . implode(' dan ', $kurang)
                . '. Jangan menebak, tunggu jawaban user.';
        }

        $nomor = PengaturanSurat::getSetting()->generateNomor(1);

        Cache::put($this->draftKey($user), [
            'jenis_surat' => $jenis,
            'alamat_tujuan' => $tujuan,
            'nomor' => $nomor,
        ], now()->addMinutes(self::DRAFT_TTL_MINUTES));

        // Tandai: draft dibuat di request ini, konfirmasi tidak boleh menyusul di giliran yang sama
        request()->attributes->set('surat_drafted_now', true);

        return "Draft pengambilan surat (BELUM diambil):\n"
            . "- Nomor surat: {$nomor}\n"
            . "- Jenis surat: {$jenis}\n"
            . "- Tujuan surat: {$tujuan}\n\n"
            . 'Tampilkan ringkasan ini ke user dan tanyakan: "Apakah data ini sudah benar? (ya/tidak)". '
            . 'Lalu BERHENTI dan tunggu jawaban user. Jangan panggil konfirmasi_surat sekarang.';
    }

    // ------------------------------------------------------------------
    // 3. Konfirmasi -> booking
    // ------------------------------------------------------------------

    private function konfirmasi(Authenticatable $user): string
    {
        // Tidak boleh langsung menyusul draft di giliran yang sama
        if (request()->attributes->get('surat_drafted_now')) {
            return 'Draft baru saja dibuat. Tampilkan ringkasan ke user dan TUNGGU jawaban "ya" dari user '
                . 'sebelum memanggil konfirmasi_surat.';
        }

        // Pesan user saat ini harus berupa persetujuan (ya/iya/ok/setuju/dst.)
        $pesan = $this->pesanUser();
        if ($pesan !== '' && ! $this->isPersetujuan($pesan)) {
            return 'Pesan user bukan persetujuan ("ya"). Surat BELUM diambil. '
                . 'Jika user ingin mengubah data, panggil draft_surat lagi; jika membatalkan, panggil batal_surat; '
                . 'jika belum jelas, tanyakan lagi "Apakah data ini sudah benar? (ya/tidak)".';
        }

        $draft = Cache::get($this->draftKey($user));

        if (! $draft) {
            return 'Tidak ada draft surat yang menunggu konfirmasi (mungkin sudah kedaluwarsa). '
                . 'Tanyakan ulang jenis surat dan tujuan surat ke user dengan teks biasa.';
        }

        Auth::setUser($user);

        $arguments = [
            'jenis_surat' => $draft['jenis_surat'],
            'alamat_tujuan' => $draft['alamat_tujuan'],
            'isi_surat' => self::DEFAULT_ISI,
            'tanggal' => now()->toDateString(),
            // Nomor yang dijanjikan ke user. Controller menolak bila sudah diambil orang lain.
            'no_surat_hidden' => $draft['nomor'],
        ];
        $arguments = $this->dates->normalizeOptionalDate($arguments, 'tanggal');

        $beforeId = (int) Surat::where('user_id', $user->getAuthIdentifier())->max('id');
        $error = null;

        try {
            // FormRequest asli (StoreSuratRequest), supaya type-hint di controller terpenuhi
            $request = $this->support->makeFormRequest(StoreSuratRequest::class, $arguments, $user);
            (new SuratController())->store($request);
        } catch (ValidationException $e) {
            $error = implode('; ', collect($e->errors())->flatten()->all());
        } catch (Throwable $e) {
            report($e);
            $error = class_basename($e) . ': ' . $e->getMessage()
                . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')';
        }

        $this->clearFlash();

        // Berhasil/tidaknya ditentukan dari data di DB, bukan dari ada tidaknya exception
        $surat = Surat::where('user_id', $user->getAuthIdentifier())
            ->where('id', '>', $beforeId)
            ->orderBy('id', 'desc')
            ->first();

        if ($surat) {
            Cache::forget($this->draftKey($user));

            return "Surat berhasil diambil.\n"
                . "- Nomor surat: {$surat->no_surat}\n"
                . "- Jenis: {$surat->jenis_surat}\n"
                . "- Tujuan: {$surat->alamat_tujuan}\n\n"
                . 'Beri tahu user: silakan buka menu "Ambil Surat" untuk upload file surat dan '
                . 'melengkapi data lainnya (isi surat, sifat, tanggal, keterangan, dll) lewat tombol edit.';
        }

        // Gagal. Cek apakah nomor sudah diambil orang lain.
        $nomorLama = $draft['nomor'];
        $nomorBaru = PengaturanSurat::getSetting()->generateNomor(1);

        if ($nomorBaru !== $nomorLama) {
            $draft['nomor'] = $nomorBaru;
            Cache::put($this->draftKey($user), $draft, now()->addMinutes(self::DRAFT_TTL_MINUTES));

            return "Nomor {$nomorLama} sudah diambil pengguna lain, jadi surat BELUM terbuat.\n"
                . "Nomor tersedia sekarang: {$nomorBaru}\n"
                . "- Jenis surat: {$draft['jenis_surat']}\n"
                . "- Tujuan surat: {$draft['alamat_tujuan']}\n\n"
                . 'Beri tahu user nomornya berubah dan tanyakan lagi apakah mau lanjut dengan nomor baru ini (ya/tidak). '
                . 'Tunggu jawaban user.';
        }

        if ($error) {
            return "Gagal mengambil nomor surat. Penyebab: {$error}\n"
                . 'Sampaikan penyebab ini ke user PERSIS apa adanya (kutip utuh, jangan diringkas atau diparafrasekan).';
        }

        return 'Gagal mengambil nomor surat. Silakan coba lagi.';
    }

    private function batal(Authenticatable $user): string
    {
        Cache::forget($this->draftKey($user));

        return 'Draft surat dibatalkan. Nomor tidak diambil.';
    }

    // ------------------------------------------------------------------

    private function history(Authenticatable $user): string
    {
        $surats = Surat::where('user_id', $user->getAuthIdentifier())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $surats->isEmpty()
            ? 'Belum ada riwayat surat.'
            : $surats->map(fn ($s) => "- No: {$s->no_surat} ({$s->jenis_surat})")->implode("\n");
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    /** Pesan user pada request chat saat ini (kosong bila tidak tersedia, guard dilewati). */
    private function pesanUser(): string
    {
        try {
            $pesan = request()->input('message', '');

            return is_string($pesan) ? mb_strtolower(trim($pesan)) : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    /** Apakah minimal satu kata bermakna dari nilai-nilai itu benar-benar disebut di pesan user. */
    private function disebutUser(string $pesan, array $nilai): bool
    {
        foreach ($nilai as $teks) {
            $teks = mb_strtolower(trim((string) $teks));
            if ($teks === '') {
                continue;
            }

            if (str_contains($pesan, $teks)) {
                return true;
            }

            foreach (preg_split('/[^\p{L}\p{N}]+/u', $teks, -1, PREG_SPLIT_NO_EMPTY) as $kata) {
                if (mb_strlen($kata) < 3 || in_array($kata, self::STOPWORDS, true)) {
                    continue;
                }
                if (str_contains($pesan, $kata)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isPersetujuan(string $pesan): bool
    {
        $batas = '(?<![\p{L}\p{N}])%s(?![\p{L}\p{N}])';

        // "ya tapi ganti tujuan" / "tidak" / "batal" bukan persetujuan
        if (preg_match('/' . sprintf($batas, '(tidak|nggak|gak|bukan|batal|jangan|salah|ganti|ubah|tunggu)') . '/u', $pesan)) {
            return false;
        }

        return (bool) preg_match(
            '/' . sprintf($batas, '(ya|iya|iyaa|yup|yes|y|ok|oke|okay|betul|benar|setuju|lanjut|gas|sip|siap|boleh)') . '/u',
            $pesan
        );
    }

    /** store() men-flash pesan ke session; hapus agar tidak muncul di halaman berikutnya. */
    private function clearFlash(): void
    {
        try {
            $current = request();
            if ($current->hasSession()) {
                $current->session()->forget(['success', 'error', '_old_input']);
            }
        } catch (Throwable $e) {
            // abaikan
        }
    }

    private function draftKey(Authenticatable $user): string
    {
        return 'chatbot:surat_draft:' . $user->getAuthIdentifier();
    }
}