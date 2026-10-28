<?php

namespace App\Services\Chatbot;

use App\Models\ChatHistory;
use App\Services\Chatbot\Tools\ToolRegistry;
use App\Services\GroqRateLimit;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * LAPIS 7: Komunikasi dengan Groq (OpenAI-compatible) termasuk Function Calling.
 *
 * Alur: ask() -> (jika AI memanggil tool) handleToolCalls() -> ToolRegistry::run() -> askFollowUp()
 */
class GroqChat
{
    // ── Pengaturan hemat token ────────────────────────────────────
    private const CACHE_TTL_NORMAL = 7200;   // 2 jam

    private const CACHE_TTL_LIVE = 600;    // 10 menit

    private const MAX_TOKENS_NORMAL = 800;

    private const MAX_TOKENS_LIVE = 1500;

    private const GROQ_CALLS_PER_MIN_PER_IP = 8;

    // Batas history yang dikirim ke AI
    private const HISTORY_LIMIT = 10;

    public function __construct(
        private PromptBuilder $prompts,
        private ToolRegistry $tools,
    ) {
    }

    /**
     * DITAMBAHKAN LOGIKA FUNCTION CALLING DI SINI
     * [FIX] Parameter $isAction: jika true, jawaban tidak disimpan ke cache
     * (karena jawaban aksi bergantung pada konteks percakapan, mis. "ya").
     */
    public function ask(string $message, string $lower, string $cacheKey, string $sessionId, ?int $userId, bool $isAction = false)
    {
        $apiKey = config('services.groq.key');
        $baseUrl = config('services.groq.url');
        $model = config('services.groq.model', 'openai/gpt-oss-20b');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'GROQ_API_KEY belum di-set di .env',
            ], 500);
        }

        $rlKey = 'chatbot-groq:'.request()->ip();
        if (RateLimiter::tooManyAttempts($rlKey, self::GROQ_CALLS_PER_MIN_PER_IP)) {
            $wait = RateLimiter::availableIn($rlKey);

            return response()->json([
                'error' => "Terlalu banyak pertanyaan. Coba lagi dalam {$wait} detik.",
            ], 429);
        }
        RateLimiter::hit($rlKey, 60);

        $isGptOss = str_contains($model, 'gpt-oss');
        // [FIX] Niat aksi (mis. "cek aula besok") jangan memicu web search: boros token & memicu limit
        $live = $isGptOss && ! $isAction && $this->needsWebSearch($lower);

        try {
            $history = ChatHistory::recentForSession($sessionId, self::HISTORY_LIMIT);

            if (! empty($history) && end($history)['role'] === 'user' && end($history)['content'] === $message) {
                array_pop($history);
            }

            $messages = array_merge(
                [['role' => 'system', 'content' => $isAction
                    ? $this->prompts->action($message)
                    : $this->prompts->system($message, $live)]],
                $history,
                [['role' => 'user', 'content' => $message]]
            );

            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.4,
                'max_tokens' => $live ? self::MAX_TOKENS_LIVE : self::MAX_TOKENS_NORMAL,
            ];

            if ($isGptOss) {
                $payload['reasoning_effort'] = 'low';
            }

            // TAMBAHAN: DAFTARKAN TOOLS UNTUK FUNCTION CALLING
            $payload['tools'] = $this->tools->definitions();
            $payload['tool_choice'] = 'auto';

            if ($live) {
                // Browser search tetap dipertahankan jika mode live
                $payload['tools'][] = ['type' => 'browser_search'];
            }

            $response = Http::timeout($live ? 30 : 15)
                ->retry(2, 500, function ($exception) use ($isAction) {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    // [FIX] Untuk aksi, 429 tidak di-retry (retry hanya menghabiskan kuota lagi)
                    return $exception instanceof RequestException
                        && in_array($exception->response->status(), $isAction ? [500, 502, 503] : [429, 500, 502, 503]);
                }, throw: false)
                ->withToken($apiKey)
                ->acceptJson()
                ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

            try {
                GroqRateLimit::capture($response);
            } catch (\Throwable $e) {
                Log::warning('GroqRateLimit capture gagal', ['message' => $e->getMessage()]);
            }

            // [FIX] gpt-oss kadang menghasilkan tool call rusak -> Groq balas 400 "tool_use_failed".
            // Coba ulang sekali dengan setelan lebih deterministik.
            if ($response->status() === 400 && str_contains($response->body(), 'tool_use_failed')) {
                Log::warning('Groq tool_use_failed, mencoba ulang', ['body' => $response->body()]);

                $payload['temperature'] = 0.1;
                $payload['max_tokens'] = max((int) $payload['max_tokens'], 1200);
                if ($isGptOss) {
                    $payload['reasoning_effort'] = 'medium';
                }

                $response = Http::timeout(30)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

                try {
                    GroqRateLimit::capture($response);
                } catch (\Throwable $e) {
                    Log::warning('GroqRateLimit capture gagal (retry)', ['message' => $e->getMessage()]);
                }
            }

            if ($response->failed()) {
                Log::error('Groq API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if ($response->status() === 429) {
                    $wait = (int) ceil((float) $response->header('retry-after'));
                    $reply = $wait > 0
                        ? "Maaf, AI sedang kena batas pemakaian. Coba lagi dalam {$wait} detik ya."
                        : 'Maaf, saya sedang sibuk sebentar ini. Coba tanya lagi dalam 1-2 menit ya.';
                    ChatHistory::log($sessionId, 'assistant', $reply, $userId);

                    return response()->json([
                        'reply' => $reply,
                        'source' => 'rate_limited',
                    ]);
                }

                if ($response->status() === 400) {
                    $reply = 'Maaf, AI gagal memproses permintaan itu. Coba ulangi dengan kalimat yang lebih singkat, misalnya: "booking aula Wiradadahaa tanggal 29 Oktober sesi pagi".';
                    ChatHistory::log($sessionId, 'assistant', $reply, $userId);

                    return response()->json([
                        'reply' => $reply,
                        'source' => 'groq_error',
                    ]);
                }

                return response()->json([
                    'error' => 'Gagal menghubungi AI (HTTP '.$response->status().')',
                    'detail' => app()->isLocal() ? $response->json() : null,
                ], 500);
            }

            $responseData = $response->json() ?? [];
            $choice = $responseData['choices'][0] ?? null;

            // [FIX] Pengaman jika API membalas tanpa "choices"
            if ($choice === null) {
                Log::warning('Groq balas tanpa choices', ['body' => $responseData]);

                $reply = 'Maaf, saya belum bisa memproses itu sekarang. Coba ulangi sebentar lagi ya.';
                ChatHistory::log($sessionId, 'assistant', $reply, $userId);

                return response()->json([
                    'reply' => $reply,
                    'source' => 'groq',
                ]);
            }

            // CEK APAKAH AI INGIN MEMANGGIL TOOL
            if (isset($choice['message']['tool_calls']) && count($choice['message']['tool_calls']) > 0) {
                return $this->handleToolCalls($choice['message'], $messages, $sessionId, $userId, $apiKey, $baseUrl, $model);
            }

            $reply = (string) ($choice['message']['content'] ?? '');
            $reply = trim(preg_replace('/【[^】]*】/u', '', $reply));

            if ($reply === '') {
                Log::warning('Groq balas kosong', ['body' => $response->json()]);

                $reply = 'Maaf, saya belum bisa menjawab itu. Coba ulangi dengan kalimat lain.';
                ChatHistory::log($sessionId, 'assistant', $reply, $userId);

                return response()->json([
                    'reply' => $reply,
                    'source' => 'groq',
                ]);
            }

            ChatHistory::log($sessionId, 'assistant', $reply, $userId);

            // [FIX] Jawaban untuk niat aksi tidak di-cache (bergantung konteks percakapan)
            if (! $isAction) {
                Cache::put($cacheKey, $reply, $live ? self::CACHE_TTL_LIVE : self::CACHE_TTL_NORMAL);
            }

            return response()->json([
                'reply' => $reply,
                'source' => $live ? 'groq_search' : 'groq',
                'usage' => app()->isLocal() ? $response->json('usage') : null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Chatbot Exception', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    private function handleToolCalls(array $message, array $history, string $sessionId, ?int $userId, string $apiKey, string $baseUrl, string $model)
    {
        $toolCalls = $message['tool_calls'];

        // [FIX] Follow-up dibuat RINGAN: system prompt ringkas + 6 pesan terakhir (agar AI tidak lupa konteks)
        // tanpa mengirim ulang seluruh knowledge base -> hemat token, tidak mudah kena limit.
        $context = [];
        foreach ($history as $m) {
            if (in_array($m['role'] ?? '', ['user', 'assistant'], true) && ! empty($m['content']) && empty($m['tool_calls'])) {
                $context[] = ['role' => $m['role'], 'content' => mb_substr((string) $m['content'], 0, 400)];
            }
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->prompts->followUp()]],
            array_slice($context, -6)
        );

        // Pesan assistant dikirim balik dalam format bersih (tanpa field tambahan seperti "reasoning")
        $messages[] = [
            'role' => 'assistant',
            'content' => (string) ($message['content'] ?? ''),
            'tool_calls' => array_map(fn ($tc) => [
                'id' => $tc['id'],
                'type' => 'function',
                'function' => [
                    'name' => $tc['function']['name'],
                    'arguments' => is_string($tc['function']['arguments'] ?? null)
                        ? $tc['function']['arguments']
                        : json_encode($tc['function']['arguments'] ?? new \stdClass()),
                ],
            ], $toolCalls),
        ];

        $user = Auth::user();
        $summaries = [];

        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'];
            $arguments = json_decode($toolCall['function']['arguments'] ?? '', true);
            $arguments = is_array($arguments) ? $arguments : [];

            // Eksekusi tool dilakukan oleh ToolRegistry (lihat folder Tools/)
            [$result, $summary] = $this->tools->run($functionName, $arguments, $user);

            $summaries[] = $summary;

            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'content' => $result,
            ];
        }

        return $this->askFollowUp($messages, $sessionId, $userId, $apiKey, $baseUrl, $model, $summaries);
    }

    /**
     * Follow-up ke AI untuk menyusun balasan setelah tool dijalankan.
     * Jika AI gagal / kena limit, hasil tool tetap ditampilkan ke user ($fallbackLines).
     */
    private function askFollowUp(array $messages, string $sessionId, ?int $userId, string $apiKey, string $baseUrl, string $model, array $fallbackLines = [])
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.4,
            'max_tokens' => self::MAX_TOKENS_NORMAL,
        ];

        if (str_contains($model, 'gpt-oss')) {
            $payload['reasoning_effort'] = 'low';
        }

        $fallbackReply = $fallbackLines !== []
            ? implode("\n", $fallbackLines)
            : 'Aksi sudah diproses. Silakan cek hasilnya di menu terkait ya.';

        try {
            $response = Http::timeout(20)
                // Retry hanya untuk gangguan koneksi, BUKAN untuk 429 (retry hanya menghabiskan kuota lagi)
                ->retry(2, 500, fn ($exception) => $exception instanceof ConnectionException, throw: false)
                ->withToken($apiKey)
                ->acceptJson()
                ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

            try {
                GroqRateLimit::capture($response);
            } catch (\Throwable $e) {
                Log::warning('GroqRateLimit capture gagal (follow up)', ['message' => $e->getMessage()]);
            }

            $data = $response->json() ?? [];

            // Pengaman agar tidak terjadi "Undefined array key choices" jika Groq membalas error
            if (isset($data['error']) || !isset($data['choices'][0]['message']['content'])) {
                Log::error('Groq follow up error', [
                    'status' => $response->status(),
                    'body' => $data ?: $response->body(),
                ]);

                // Aksi sudah terlanjur dijalankan -> tampilkan hasilnya langsung
                ChatHistory::log($sessionId, 'assistant', $fallbackReply, $userId);

                return response()->json(['reply' => $fallbackReply, 'source' => 'tool_fallback']);
            }

            $reply = (string) $data['choices'][0]['message']['content'];
            $reply = trim(preg_replace('/【[^】]*】/u', '', $reply));

            if ($reply === '') {
                $reply = $fallbackReply;
            }

            ChatHistory::log($sessionId, 'assistant', $reply, $userId);

            return response()->json(['reply' => $reply, 'source' => 'groq_tool']);
        } catch (\Throwable $e) {
            Log::error('Groq follow up exception', ['message' => $e->getMessage()]);

            ChatHistory::log($sessionId, 'assistant', $fallbackReply, $userId);

            return response()->json(['reply' => $fallbackReply, 'source' => 'tool_fallback']);
        }
    }

    private function needsWebSearch(string $lower): bool
    {
        return (bool) preg_match(
            '/\b(jadwal|skor|klasemen|hasil pertandingan|hari ini|besok|kemarin|terbaru|terkini|berita|cuaca|harga|kurs|saham|update|live|covid|vaksin|penyakit|resep|restoran|hotel)\b/iu',
            $lower
        );
    }
}
