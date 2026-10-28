<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use App\Services\Chatbot\ActionIntent;
use App\Services\Chatbot\Answers\AulaAnswer;
use App\Services\Chatbot\Answers\OrganizationAnswer;
use App\Services\Chatbot\Answers\VehicleAnswer;
use App\Services\Chatbot\GroqChat;
use App\Services\Chatbot\TemplateService;
use App\Services\GroqRateLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Controller chatbot (tipis). Logika dipisah ke app/Services/Chatbot/:
 *
 *  ActionIntent            deteksi niat aksi (booking, surat, dst.)
 *  Answers/AulaAnswer      LAPIS 1  data aula
 *  Answers/VehicleAnswer   LAPIS 2-3 data kendaraan & pajak
 *  Answers/OrganizationAnswer LAPIS 4 data pegawai
 *  TemplateService         LAPIS 5  template & menu cepat
 *  GroqChat                LAPIS 7  AI + function calling
 *    PromptBuilder, KnowledgeBase   penyusun prompt
 *    Tools/*                        tool per domain (BookingTools, SuratTools, AbsensiTools)
 */
class ChatbotController extends Controller
{
    public function __construct(
        private ActionIntent $intent,
        private AulaAnswer $aulaAnswer,
        private VehicleAnswer $vehicleAnswer,
        private OrganizationAnswer $organizationAnswer,
        private TemplateService $templates,
        private GroqChat $groq,
    ) {
    }

    /**
     * Endpoint utama chat.
     */
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

        // [FIX] PENGAMAN AKSI SISTEM:
        // Jika user berniat melakukan aksi (booking, surat, absensi, riwayat, konfirmasi "ya", dll),
        // lewati pencarian manual (Lapis 1-6) dan langsung kirim ke AI (Groq) agar Function Calling berjalan.
        if ($this->intent->isActionIntent($lower) || $this->intent->isActionContinuation($sessionId)) {
            $cacheKey = 'chatbot:ans:'.md5(preg_replace('/\s+/u', ' ', $lower));

            return $this->groq->ask($message, $lower, $cacheKey, $sessionId, $userId, true);
        }

        // LAPIS 1: PENCARIAN UMUM DI DATABASE (Aula dll)
        $generalDatabaseReply = $this->aulaAnswer->answer($lower);
        if ($generalDatabaseReply !== null) {
            ChatHistory::log($sessionId, 'assistant', $generalDatabaseReply, $userId);

            return response()->json([
                'reply' => $generalDatabaseReply,
                'source' => 'database_general',
            ]);
        }

        // LAPIS 2: DATA KENDARAAN SPESIFIK (plat / merk / pemakai)
        $vehicleReply = $this->vehicleAnswer->specific($message, $lower);
        if ($vehicleReply !== null) {
            ChatHistory::log($sessionId, 'assistant', $vehicleReply, $userId);

            return response()->json([
                'reply' => $vehicleReply,
                'source' => 'database_vehicle',
            ]);
        }

        // LAPIS 3: RINGKASAN KENDARAAN (jumlah, pajak jatuh tempo)
        $summaryReply = $this->vehicleAnswer->summary($lower);
        if ($summaryReply !== null) {
            ChatHistory::log($sessionId, 'assistant', $summaryReply, $userId);

            return response()->json([
                'reply' => $summaryReply,
                'source' => 'database_vehicle_summary',
            ]);
        }

        // LAPIS 4: DATA PEGAWAI DAN ORGANISASI
        $organizationReply = $this->organizationAnswer->answer($lower);
        if ($organizationReply !== null) {
            ChatHistory::log($sessionId, 'assistant', $organizationReply, $userId);

            return response()->json([
                'reply' => $organizationReply,
                'source' => 'database_users',
            ]);
        }

        // LAPIS 5: TEMPLATE
        $template = $this->templates->match($message);
        if ($template) {
            ChatHistory::log($sessionId, 'assistant', $template['reply'], $userId);

            return response()->json([
                'reply' => $template['reply'],
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
        return $this->groq->ask($message, $lower, $cacheKey, $sessionId, $userId);
    }

    /**
     * Endpoint untuk hapus history session user.
     */
    public function clearHistory(Request $request)
    {
        $sessionId = $request->session()->getId();
        ChatHistory::where('session_id', $sessionId)->delete();

        return response()->json(['success' => true, 'message' => 'History dibersihkan.']);
    }

    /**
     * Daftar menu cepat (DB + Config).
     */
    public function menus()
    {
        return response()->json([
            'menus' => $this->templates->menus(),
        ]);
    }

    /**
     * Ambil template berdasarkan ID (tombol menu).
     */
    public function template(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $template = $this->templates->find($request->id);

        if ($template) {
            return response()->json($template);
        }

        return response()->json(['error' => 'Template tidak ditemukan'], 404);
    }
}