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

class SuratTools implements ToolGroup
{
    private const TOOLS = [
        'cek_nomor_surat',
        'draft_surat',
        'konfirmasi_surat',
        'batal_surat',
        'get_riwayat_surat',
    ];

    private const DRAFT_TTL_MINUTES = 15;

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
                    'name' => 'cek_nomor_surat',
                    'description' => 'Cek nomor surat terakhir, nomor tersedia berikutnya, ATAU cari tahu surat dengan nomor tertentu dipakai oleh siapa. '
                        . 'Gunakan saat user bertanya "nomor terakhir berapa?", "nomor tersedia apa?", atau "nomor 123/2026 dipakai siapa/surat apa?".',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'nomor_spesifik' => [
                                'type' => 'string', 
                                'description' => 'Kosongkan jika user hanya tanya nomor terakhir/tersedia. Isi jika user mencari nomor spesifik (misal: "123/2026").'
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'draft_surat',
                    'description' => 'Siapkan draft pengambilan nomor surat. '
                        . 'ALUR: user WAJIB menyebutkan 3 hal ini: (1) Tanggal surat, (2) Jenis surat, (3) Tujuan surat. '
                        . 'JIKA SALAH SATU BELUM ADA, JANGAN panggil tool ini. Tanyakan dulu dengan teks biasa, tunggu jawaban user. '
                        . 'Panggil tool ini HANYA setelah tanggal, jenis, dan tujuan sudah lengkap.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tanggal' => ['type' => 'string', 'description' => 'Format YYYY-MM-DD'],
                            'jenis_surat' => ['type' => 'string', 'description' => 'Jenis surat, sesuai ucapan user'],
                            'alamat_tujuan' => ['type' => 'string', 'description' => 'Tujuan surat, sesuai ucapan user'],
                        ],
                        'required' => ['tanggal', 'jenis_surat', 'alamat_tujuan'],
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
            'cek_nomor_surat' => $this->cekNomor($arguments),
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
    // 1. Cek Nomor
    // ------------------------------------------------------------------

    private function cekNomor(array $arguments): string
    {
        $nomorSpesifik = trim((string) ($arguments['nomor_spesifik'] ?? ''));

        if ($nomorSpesifik !== '') {
            $surat = Surat::with('user')
                ->where('no_surat', 'LIKE', "%{$nomorSpesifik}%")
                ->first();

            if (!$surat) {
                return "Nomor surat {$nomorSpesifik} tidak ditemukan di database.";
            }

            $namaUser = $surat->user?->name ?? 'Tidak diketahui';
            return "Nomor surat {$surat->no_surat} dipakai oleh: {$namaUser}. "
                 . "Jenis surat: {$surat->jenis_surat}. Tujuan: {$surat->alamat_tujuan}.";
        }

        $next = PengaturanSurat::getSetting()->generateNomor(1);
        $last = Surat::orderBy('id', 'desc')->first();

        $lastText = $last
            ? "Nomor surat terakhir terpakai: {$last->no_surat} ({$last->jenis_surat}) oleh {$last->user?->name}"
            : 'Belum ada surat yang tercatat.';

        // [FIX] Jangan suruh isi form. Cukup beri info dan tanya apakah mau ambil nomor.
        return "{$lastText}\n"
            . "Nomor tersedia berikutnya: {$next}\n"
            . '(Tanyakan ke user: "Apakah Anda ingin mengambil nomor surat ini? Jika ya, tolong sebutkan tanggal, jenis, dan tujuan suratnya.")';
    }

    // ------------------------------------------------------------------
    // 2. Draft
    // ------------------------------------------------------------------

    private function draft(array $arguments, Authenticatable $user): string
    {
        $tanggal = trim((string) ($arguments['tanggal'] ?? ''));
        $jenis = trim((string) ($arguments['jenis_surat'] ?? ''));
        $tujuan = trim((string) ($arguments['alamat_tujuan'] ?? ''));
        
        // Guard anti-karangan
        $pesan = $this->pesanUser();
        if ($pesan !== '' && !$this->disebutUser($pesan, [$jenis, $tujuan])) {
            return 'Jenis surat dan tujuan surat tidak ditemukan di pesan user, jadi dianggap karangan. '
                . 'JANGAN mengarang. Balas teks biasa: "Silakan sebutkan tanggal, jenis surat, dan tujuan surat-nya ya." '
                . 'lalu berhenti dan tunggu jawaban user.';
        }

        $kurang = [];
        if ($tanggal === '') $kurang[] = 'tanggal surat';
        if ($jenis === '') $kurang[] = 'jenis surat';
        if ($tujuan === '') $kurang[] = 'tujuan surat';
        
        if ($kurang) {
            // [FIX] Jangan kasih format tabel kosong. Cukup minta data yang kurang dengan teks biasa.
            return 'Data belum lengkap. Tanyakan ke user dengan teks biasa: "Tolong lengkapi dulu ' . implode(', ', $kurang) . ' nya ya." '
                . 'JANGAN menampilkan format tabel/form apapun. Tunggu jawaban user.';
        }

        $argsForDate = ['tanggal' => $tanggal];
        $this->dates->normalizeOptionalDate($argsForDate, 'tanggal');
        $tanggal = $argsForDate['tanggal'];

        $nomor = PengaturanSurat::getSetting()->generateNomor(1);

        Cache::put($this->draftKey($user), [
            'tanggal' => $tanggal,
            'jenis_surat' => $jenis,
            'alamat_tujuan' => $tujuan,
            'nomor' => $nomor,
        ], now()->addMinutes(self::DRAFT_TTL_MINUTES));

        request()->attributes->set('surat_drafted_now', true);

        return "Draft pengambilan surat (BELUM diambil):\n"
            . "- Nomor surat: {$nomor}\n"
            . "- Tanggal surat: {$tanggal}\n"
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
        if (request()->attributes->get('surat_drafted_now')) {
            return 'Draft baru saja dibuat. Tampilkan ringkasan ke user dan TUNGGU jawaban "ya" dari user '
                . 'sebelum memanggil konfirmasi_surat.';
        }

        $pesan = $this->pesanUser();
        if ($pesan !== '' && ! $this->isPersetujuan($pesan)) {
            return 'Pesan user bukan persetujuan ("ya"). Surat BELUM diambil. '
                . 'Jika user ingin mengubah data, panggil draft_surat lagi; jika membatalkan, panggil batal_surat; '
                . 'jika belum jelas, tanyakan lagi "Apakah data ini sudah benar? (ya/tidak)".';
        }

        $draft = Cache::get($this->draftKey($user));

        if (! $draft) {
            return 'Tidak ada draft surat yang menunggu konfirmasi (mungkin sudah kedaluwarsa). '
                . 'Tanyakan ulang tanggal, jenis surat, dan tujuan surat ke user dengan teks biasa.';
        }

        Auth::setUser($user);

        // Lengkapi SEMUA field wajib StoreSuratRequest dengan nilai default '-'
        $arguments = [
            'jenis_surat' => $draft['jenis_surat'],
            'alamat_tujuan' => $draft['alamat_tujuan'],
            'tanggal' => $draft['tanggal'], 
            'isi_surat' => '-',
            'sifat_surat' => '-', 
            'no_indek' => '-',
            'keterangan' => '-',
            'no_surat_hidden' => $draft['nomor'],
        ];
        
        $arguments = $this->dates->normalizeOptionalDate($arguments, 'tanggal');

        $beforeId = (int) Surat::where('user_id', $user->getAuthIdentifier())->max('id');
        $error = null;

        try {
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

        $surat = Surat::where('user_id', $user->getAuthIdentifier())
            ->where('id', '>', $beforeId)
            ->orderBy('id', 'desc')
            ->first();

        if ($surat) {
            Cache::forget($this->draftKey($user));

            return "Surat berhasil diambil.\n"
                . "- Nomor surat: {$surat->no_surat}\n"
                . "- Tanggal: {$surat->tanggal}\n"
                . "- Jenis: {$surat->jenis_surat}\n"
                . "- Tujuan: {$surat->alamat_tujuan}\n\n"
                . 'Beri tahu user: silakan buka menu "Ambil Surat" untuk upload file dan melengkapi data lainnya (isi surat, sifat, keterangan, dll) lewat tombol edit.';
        }

        $nomorLama = $draft['nomor'];
        $nomorBaru = PengaturanSurat::getSetting()->generateNomor(1);

        if ($nomorBaru !== $nomorLama) {
            $draft['nomor'] = $nomorBaru;
            Cache::put($this->draftKey($user), $draft, now()->addMinutes(self::DRAFT_TTL_MINUTES));

            return "Nomor {$nomorLama} sudah diambil pengguna lain, jadi surat BELUM terbuat.\n"
                . "Nomor tersedia sekarang: {$nomorBaru}\n"
                . "- Tanggal surat: {$draft['tanggal']}\n"
                . "- Jenis surat: {$draft['jenis_surat']}\n"
                . "- Tujuan surat: {$draft['alamat_tujuan']}\n\n"
                . 'Beri tahu user nomornya berubah dan tanyakan lagi apakah mau lanjut dengan nomor baru ini (ya/tidak).';
        }

        if ($error) {
            return "Gagal mengambil nomor surat. Penyebab: {$error}\n"
                . 'Sampaikan penyebab ini ke user PERSIS apa adanya.';
        }

        return 'Gagal mengambil nomor surat. Silakan coba lagi.';
    }

    private function batal(Authenticatable $user): string
    {
        Cache::forget($this->draftKey($user));
        return 'Draft surat dibatalkan. Nomor tidak diambil.';
    }

    // ------------------------------------------------------------------
    // Helper & History
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

    private function pesanUser(): string
    {
        try {
            $pesan = request()->input('message', '');
            return is_string($pesan) ? mb_strtolower(trim($pesan)) : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    private function disebutUser(string $pesan, array $nilai): bool
    {
        foreach ($nilai as $teks) {
            $teks = mb_strtolower(trim((string) $teks));
            if ($teks === '') continue;

            if (str_contains($pesan, $teks)) return true;

            foreach (preg_split('/[^\p{L}\p{N}]+/u', $teks, -1, PREG_SPLIT_NO_EMPTY) as $kata) {
                if (mb_strlen($kata) < 3 || in_array($kata, self::STOPWORDS, true)) continue;
                if (str_contains($pesan, $kata)) return true;
            }
        }
        return false;
    }

    private function isPersetujuan(string $pesan): bool
    {
        $batas = '(?<![\p{L}\p{N}])%s(?![\p{L}\p{N}])';

        if (preg_match('/' . sprintf($batas, '(tidak|nggak|gak|bukan|batal|jangan|salah|ganti|ubah|tunggu)') . '/u', $pesan)) {
            return false;
        }

        return (bool) preg_match(
            '/' . sprintf($batas, '(ya|iya|iyaa|yup|yes|y|ok|oke|okay|betul|benar|setuju|lanjut|gas|sip|siap|boleh)') . '/u',
            $pesan
        );
    }

    private function clearFlash(): void
    {
        try {
            $current = request();
            if ($current->hasSession()) {
                $current->session()->forget(['success', 'error', '_old_input']);
            }
        } catch (Throwable $e) {}
    }

    private function draftKey(Authenticatable $user): string
    {
        return 'chatbot:surat_draft:' . $user->getAuthIdentifier();
    }
}   