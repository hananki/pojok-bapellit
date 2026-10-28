<?php

namespace App\Services\Chatbot;

use App\Models\Aula;
use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Knowledge base yang disisipkan ke prompt AI: template, data kendaraan/pegawai, dan data aula (+ID).
 */
class KnowledgeBase
{
    private const CACHE_TTL_KB = 600;    // 10 menit cache knowledge base

    private const KB_TOP_TEMPLATES = 3;

    private const KB_CHARS_PER_TEMPLATE = 500;

    private const KB_MAX_VEHICLES = 100;

    private const KB_MAX_USERS = 200;

    public function __construct(private TemplateService $templates)
    {
    }

    private function tokenize(string $text): array
    {
        static $stop = [
            'yang', 'dan', 'untuk', 'apa', 'itu', 'ini', 'saya', 'aku', 'kamu', 'dong',
            'nih', 'tolong', 'bisa', 'ada', 'dengan', 'atau', 'kapan', 'siapa', 'mau',
            'pengen', 'gimana', 'bagaimana', 'cara', 'dari', 'ke', 'di', 'yah', 'deh',
        ];

        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_filter(
            $words,
            fn ($w) => mb_strlen($w) >= 3 && ! in_array($w, $stop, true)
        )));
    }

    public function needsDatabaseContext(string $lower): bool
    {
        $keywords = [
            'kendaraan', 'mobil', 'motor', 'plat', 'pajak', 'pemakai', 'dipakai', 'pakai',
            'merk', 'jatuh tempo', 'pajak tahunan', 'pajak 5',
            'pegawai', 'user', 'karyawan', 'staf', 'bidang', 'jabatan', 'kepala', 'sekretaris',
            'aula', 'ruangan', 'ruang', 'lokasi', 'gedung',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    public function dynamic(): string
    {
        return Cache::remember('chatbot:kb:dynamic', self::CACHE_TTL_KB, function () {
            $kb = [];

            $vehicles = Kendaraan::with(['pajakTahunanAktif', 'pajakLimaTahunanAktif'])
                ->limit(self::KB_MAX_VEHICLES)
                ->get();

            if ($vehicles->isNotEmpty()) {
                $kb[] = 'DATA KENDARAAN ('.$vehicles->count().' unit):';
                foreach ($vehicles as $k) {
                    $line = "- {$k->plat_nomor}";
                    if ($k->merk) {
                        $line .= " ({$k->merk})";
                    }
                    if ($k->pemakai) {
                        $line .= " — pemakai: {$k->pemakai}";
                    }
                    if ($k->unit_kerja) {
                        $line .= " — unit: {$k->unit_kerja}";
                    }

                    if ($k->pajakTahunanAktif) {
                        $line .= ' — pajak tahunan jatuh tempo: '.$k->pajakTahunanAktif->tanggal_jatuh_tempo->format('d-m-Y');
                    }
                    if ($k->pajakLimaTahunanAktif) {
                        $line .= ' — pajak 5 tahunan: '.$k->pajakLimaTahunanAktif->tanggal_jatuh_tempo->format('d-m-Y');
                    }
                    $kb[] = $line;
                }
            }

            $users = User::where('status', 'aktif')
                ->limit(self::KB_MAX_USERS)
                ->get(['name', 'jabatan', 'bidang']);

            if ($users->isNotEmpty()) {
                $kb[] = "\nDATA PEGAWAI AKTIF (".$users->count().' orang):';
                foreach ($users as $u) {
                    $kb[] = "- {$u->name}".($u->jabatan ? " — {$u->jabatan}" : '').($u->bidang ? " — {$u->bidang}" : '');
                }
            }

            // [FIX] Sertakan ID aula supaya AI bisa mengisi parameter aula_id pada tool booking
            $aulas = Aula::all(['id', 'nama', 'lokasi']);
            if ($aulas->isNotEmpty()) {
                $kb[] = "\nDATA AULA (aula_id untuk booking tertulis sebagai ID):";
                foreach ($aulas as $a) {
                    $lokasi = trim(preg_replace('/[📍📌]/u', '', (string) $a->lokasi));
                    $kb[] = "- [ID {$a->id}] {$a->nama}".($lokasi !== '' ? " (Lokasi: {$lokasi})" : '');
                }
            }

            return implode("\n", $kb);
        });
    }

    /**
     * [FIX] Knowledge base ringkas khusus aula (+ID), dipakai saat user berniat melakukan aksi
     * supaya AI selalu tahu aula_id tanpa memuat seluruh data kendaraan/pegawai.
     */
    public function aula(): string
    {
        return Cache::remember('chatbot:kb:aula', self::CACHE_TTL_KB, function () {
            $aulas = Aula::all(['id', 'nama', 'lokasi']);

            if ($aulas->isEmpty()) {
                return '';
            }

            $kb = ['DATA AULA (aula_id untuk booking tertulis sebagai ID):'];
            foreach ($aulas as $a) {
                $lokasi = trim(preg_replace('/[📍📌]/u', '', (string) $a->lokasi));
                $kb[] = "- [ID {$a->id}] {$a->nama}".($lokasi !== '' ? " (Lokasi: {$lokasi})" : '');
            }

            return implode("\n", $kb);
        });
    }

    private function buildTemplateKnowledgeBase(string $message): string
    {
        $words = $this->tokenize($message);
        $scored = [];

        foreach ($this->templates->allTemplates() as $t) {
            $hay = mb_strtolower(
                $t['label'].' '.implode(' ', $this->templates->normalizeKeywords($t['keywords'])).' '.$t['reply']
            );

            $score = 0;
            foreach ($words as $w) {
                if (str_contains($hay, $w)) {
                    $score++;
                }
            }
            $scored[] = ['score' => $score, 't' => $t];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $titles = Str::limit(implode(', ', array_map(fn ($s) => $s['t']['label'], $scored)), 400);
        $out = "Topik tersedia di sistem: {$titles}";

        $top = array_slice(array_filter($scored, fn ($s) => $s['score'] > 0), 0, self::KB_TOP_TEMPLATES);
        foreach ($top as $s) {
            $out .= "\n\n### ".$s['t']['label']."\n"
                .Str::limit(trim($s['t']['reply']), self::KB_CHARS_PER_TEMPLATE);
        }

        return $out;
    }

    public function build(string $message): string
    {
        $out = $this->buildTemplateKnowledgeBase($message);
        $lower = mb_strtolower($message);

        if ($this->needsDatabaseContext($lower)) {
            $dynamic = $this->dynamic();
            if ($dynamic !== '') {
                $out .= "\n\n=== DATA DATABASE ===\n".$dynamic;
            }
        } else {
            // [FIX] Tools selalu aktif -> AI SELALU perlu tahu daftar aula + ID agar bisa mengisi aula_id
            $aulaKb = $this->aula();
            if ($aulaKb !== '') {
                $out .= "\n\n=== DATA DATABASE ===\n".$aulaKb;
            }
        }

        return $out;
    }
}
