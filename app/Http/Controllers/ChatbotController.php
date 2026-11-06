<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use App\Services\Chatbot\ActionIntent;
use App\Services\Chatbot\Answers\AulaAnswer;
use App\Services\Chatbot\Answers\OrganizationAnswer;
use App\Services\Chatbot\Answers\VehicleAnswer;
use App\Services\Chatbot\GroqChat;
use App\Services\Chatbot\TemplateService;
use App\Services\Chatbot\Tools\ToolRegistry;
use App\Services\GroqRateLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    public function __construct(
        private ActionIntent $intent,
        private AulaAnswer $aulaAnswer,
        private VehicleAnswer $vehicleAnswer,
        private OrganizationAnswer $organizationAnswer,
        private TemplateService $templates,
        private GroqChat $groq,
        private ToolRegistry $tools,
    ) {
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = trim($request->message);
        $lower = mb_strtolower($message);
        $sessionId = $request->session()->getId();
        $userId = Auth::id();

        ChatHistory::log($sessionId, 'user', $message, $userId);

        // LAPIS 0: CEK LIMIT
        $limitKeywords = [
            'cek limit', 'sisa limit', 'limit ai', 'limit groq',
            'rate limit', 'berapa limit', 'quota', 'kuota',
            'sisa kuota', 'cek kuota',
        ];

        foreach ($limitKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $reply = GroqRateLimit::formatForChat();
                ChatHistory::log($sessionId, 'assistant', $reply, $userId);

                return response()->json([
                    'reply' => $reply,
                    'source' => 'limit',
                ]);
            }
        }

        // =====================================================================
        // [FIX TOTAL] PENCEGAT MANUAL ABSENSI
        // =====================================================================
        $isLihatSesi = preg_match('/\b(ada sesi|lihat sesi|liat sesi|sesi apa|sesi hari ini|sesi kemarin|kemarin ada|sesi baru ga|riwayat sesi|riwayat absen|riwayat absensi|absen saya|ga masuk|tidak masuk)\b/iu', $lower) === 1;

        if ($isLihatSesi) {
            // Cek apakah user minta riwayat pribadinya sendiri
            $isRiwayatSaya = preg_match('/\b(riwayat absen|riwayat absensi|absen saya|ga masuk|tidak masuk)\b/iu', $lower) === 1;

            if ($isRiwayatSaya) {
                // Panggil get_absensi_saya (riwayat pribadi)
                [$result, $summary] = $this->tools->run('get_absensi_saya', [], Auth::user());
            } else {
                // Panggil get_absensi_sesi (lihat daftar sesi)
                $filterWaktu = str_contains($lower, 'kemarin') ? 'kemarin' : 'hari_ini';
                [$result, $summary] = $this->tools->run('get_absensi_sesi', ['filter_waktu' => $filterWaktu], Auth::user());
            }

            ChatHistory::log($sessionId, 'assistant', $summary, $userId);

            return response()->json([
                'reply' => $summary,
                'source' => 'manual_tool',
            ]);
        }

        // [FIX] PENGAMAN AKSI SISTEM:
        if ($this->intent->isActionIntent($lower) || $this->intent->isActionContinuation($sessionId)) {
            $cacheKey = 'chatbot:ans:'.md5(preg_replace('/\s+/u', ' ', $lower));

            // Bungkus hasil Groq dengan cleaner + normalizer
            return $this->groqResponse($message, $lower, $cacheKey, $sessionId, $userId, true);
        }

        // LAPIS 1: PENCARIAN UMUM DI DATABASE (Aula dll)
        $generalDatabaseReply = $this->aulaAnswer->answer($lower);
        if ($generalDatabaseReply !== null) {
            $reply = $this->cleanReply($generalDatabaseReply);
            ChatHistory::log($sessionId, 'assistant', $reply, $userId);

            return response()->json([
                'reply' => $reply,
                'source' => 'database_general',
            ]);
        }

        // LAPIS 2: DATA KENDARAAN SPESIFIK
        $vehicleReply = $this->vehicleAnswer->specific($message, $lower);
        if ($vehicleReply !== null) {
            $reply = $this->cleanReply($vehicleReply);
            ChatHistory::log($sessionId, 'assistant', $reply, $userId);

            return response()->json([
                'reply' => $reply,
                'source' => 'database_vehicle',
            ]);
        }

        // LAPIS 3: RINGKASAN KENDARAAN
        $summaryReply = $this->vehicleAnswer->summary($lower);
        if ($summaryReply !== null) {
            $reply = $this->cleanReply($summaryReply);
            ChatHistory::log($sessionId, 'assistant', $reply, $userId);

            return response()->json([
                'reply' => $reply,
                'source' => 'database_vehicle_summary',
            ]);
        }

        // LAPIS 4: DATA PEGAWAI DAN ORGANISASI
        $organizationReply = $this->organizationAnswer->answer($lower);
        if ($organizationReply !== null) {
            $reply = $this->cleanReply($organizationReply);
            ChatHistory::log($sessionId, 'assistant', $reply, $userId);

            return response()->json([
                'reply' => $reply,
                'source' => 'database_users',
            ]);
        }

        // LAPIS 5: TEMPLATE
        $template = $this->templates->match($message);
        if ($template) {
            $reply = $this->cleanReply($template['reply']);
            ChatHistory::log($sessionId, 'assistant', $reply, $userId);

            return response()->json([
                'reply' => $reply,
                'source' => $template['source'] ?? 'template',
                'id' => $template['id'] ?? null,
            ]);
        }

        // LAPIS 6: CACHE
        $cacheKey = 'chatbot:ans:'.md5(preg_replace('/\s+/u', ' ', $lower));
        if ($cached = Cache::get($cacheKey)) {
            ChatHistory::log($sessionId, 'assistant', $cached, $userId);

            return response()->json([
                'reply' => $cached,
                'source' => 'cache',
            ]);
        }

        // LAPIS 7: GROQ
        return $this->groqResponse($message, $lower, $cacheKey, $sessionId, $userId);
    }

    public function clearHistory(Request $request)
    {
        $sessionId = $request->session()->getId();
        ChatHistory::where('session_id', $sessionId)->delete();

        return response()->json(['success' => true, 'message' => 'History dibersihkan.']);
    }

    public function menus()
    {
        return response()->json([
            'menus' => $this->templates->menus(),
        ]);
    }

    public function template(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $template = $this->templates->find($request->id);

        if ($template) {
            // Bersihkan reply template juga (jaga-jaga kalau di DB ada **, dll)
            $template['reply'] = $this->cleanReply($template['reply'] ?? '');

            return response()->json($template);
        }

        return response()->json(['error' => 'Template tidak ditemukan'], 404);
    }

    // =====================================================================
    // HELPER: Bungkus panggilan Groq + bersihkan jawaban + cache hasil bersih
    // =====================================================================
    private function groqResponse(
        string $message,
        string $lower,
        string $cacheKey,
        string $sessionId,
        ?int $userId,
        bool $isAction = false
    ) {
        $response = $this->groq->ask($message, $lower, $cacheKey, $sessionId, $userId, $isAction);

        // Kalau response adalah JsonResponse dari GroqChat, ambil isinya
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);

            if (!empty($data['reply']) && is_string($data['reply'])) {
                $data['reply'] = $this->cleanReply($data['reply']);

                // Simpan versi bersih ke cache (kecuali kalau ini aksi sistem)
                if (!$isAction && empty($data['error'])) {
                    Cache::put($cacheKey, $data['reply'], now()->addHours(6));
                }
            }

            return response()->json($data, $response->getStatusCode());
        }

        // Fallback kalau GroqChat mengembalikan tipe lain
        return $response;
    }

    // =====================================================================
    // HELPER: Bersihkan markdown + rapikan spasi/baris
    // =====================================================================
    private function cleanReply(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        // 1. Hapus bold/italic markdown: **teks**, *teks*, __teks__, _teks_
        $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text);
        $text = preg_replace('/\*(.+?)\*/s', '$1', $text);
        $text = preg_replace('/__(.+?)__/s', '$1', $text);
        $text = preg_replace('/(?<!\w)_(.+?)_(?!\w)/s', '$1', $text);

        // 2. Hapus heading markdown: ##, ###, dst di awal baris
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);

        // 3. Hapus garis pemisah: ---, ***, ___
        $text = preg_replace('/^[-*_]{3,}\s*$/m', '', $text);

        // 4. Bullet markdown "* " atau "+ " → ganti jadi "- "
        $text = preg_replace('/^[\*\+]\s+/m', '- ', $text);

        // 5. Hapus code fence & inline code
        $text = preg_replace('/```[\s\S]*?```/', '', $text);
        $text = preg_replace('/`([^`]+)`/', '$1', $text);

        // 6. Hapus link markdown [teks](url) → teks
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);

        // 7. Hapus karakter formatting sisa
        $text = str_replace(['**', '__', '~~'], '', $text);

        // 8. Normalisasi banyak newline beruntun → maksimal 2
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // 9. Rapikan spasi ganda (kecuali newline)
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);

        // 10. Batasi panjang maksimal ~600 karakter (biar tidak jadi essay)
        if (mb_strlen($text) > 600) {
            $text = mb_substr($text, 0, 600);
            // Potong di kata terakhir biar tidak kepotong tengah kata
            $lastSpace = mb_strrpos($text, ' ');
            if ($lastSpace !== false && $lastSpace > 500) {
                $text = mb_substr($text, 0, $lastSpace);
            }
            $text = rtrim($text, " \t\n.,;:") . '…';
        }

        return trim($text);
    }
}