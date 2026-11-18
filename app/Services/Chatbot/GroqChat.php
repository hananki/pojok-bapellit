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
 */
class GroqChat
{
    // ── Pengaturan hemat token ────────────────────────────────────
    private const CACHE_TTL_NORMAL = 7200;   // 2 jam
    private const CACHE_TTL_LIVE = 600;      // 10 menit
    private const MAX_TOKENS_NORMAL = 500;
    private const MAX_TOKENS_LIVE = 900;
    private const GROQ_CALLS_PER_MIN_PER_IP = 60; 
    private const RATE_LIMIT_COOLDOWN = 15;
    private const HISTORY_LIMIT = 10;
    private const MAX_REPLY_CHARS = 600;

    private const ACTION_DEDUP_TTL = 30;

    public function __construct(
        private PromptBuilder $prompts,
        private ToolRegistry $tools,
    ) {
    }

    private function getApiKey(): string
    {
        $keys = config('services.groq.keys', []);
        if (empty($keys)) {
            return '';
        }
        $randomIndex = array_rand($keys);
        return $keys[$randomIndex];
    }

    public function ask(string $message, string $lower, string $cacheKey, string $sessionId, ?int $userId, bool $isAction = false)
    {
        $baseUrl = config('services.groq.url');
        $model = config('services.groq.model', 'openai/gpt-oss-20b');
        
        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            return response()->json(['error' => 'GROQ_API_KEY belum di-set di .env'], 500);
        }

        // Deteksi obrolan santai biar nggak kena cache
        $isCasual = preg_match('/\b(halo|hai|pagi|siang|malam|kabar|sayang|jago|ga|gak|kok|kenapa|lucu|anjir|wkwk|rekomendasi)\b/iu', $lower) === 1;

        if (! $isAction && ! $isCasual) {
            $cachedReply = Cache::get($cacheKey);
            if ($cachedReply) {
                ChatHistory::log($sessionId, 'assistant', $cachedReply, $userId);
                return response()->json(['reply' => $cachedReply, 'source' => 'cache']);
            }
        }

        // Circuit breaker (Kunci per-IP)
        $cooldownUntil = (int) Cache::get('chatbot:groq:cooldown_until', 0);
        if ($cooldownUntil > time()) {
            $wait = $cooldownUntil - time();
            $reply = "AI sedang istirahat sebentar (kena batas). Coba lagi dalam {$wait} detik ya 🙏";
            ChatHistory::log($sessionId, 'assistant', $reply, $userId);
            return response()->json(['reply' => $reply, 'source' => 'cooldown']);
        }

        $rlKey = 'chatbot-groq:'.request()->ip();
        if (RateLimiter::tooManyAttempts($rlKey, self::GROQ_CALLS_PER_MIN_PER_IP)) {
            $wait = RateLimiter::availableIn($rlKey);
            return response()->json(['error' => "Kamu terlalu cepat bertanya. Tunggu {$wait} detik ya."], 429);
        }
        RateLimiter::hit($rlKey, 60);

        $isGptOss = str_contains($model, 'gpt-oss');
        
        // Selalu pakai web search kalau bukan aksi sistem dan bukan pertanyaan data internal
        $isInternalQuery = preg_match('/\b(booking|boking|aula|surat|absensi|presensi|user|pegawai|kendaraan|mobil|motor|plat|pajak|bidang|jabatan)\b/iu', $lower) === 1;
        $live = $isGptOss && ! $isAction && ! $isInternalQuery;

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

            $payload['tools'] = $this->tools->definitions();
            $payload['tool_choice'] = 'auto';

            if ($live) {
                $payload['tools'][] = ['type' => 'browser_search'];
            }

            $response = Http::timeout($live ? 30 : 15)
                ->retry(1, 300, function ($exception) {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }
                    return $exception instanceof RequestException
                        && in_array($exception->response->status(), [500, 502, 503, 504]);
                }, throw: false)
                ->withToken($apiKey)
                ->acceptJson()
                ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

            try {
                GroqRateLimit::capture($response, $apiKey); // [FIX] Tambah $apiKey
            } catch (\Throwable $e) {
                Log::warning('GroqRateLimit capture gagal', ['message' => $e->getMessage()]);
            }

            if ($response->status() === 400 && str_contains($response->body(), 'tool_use_failed')) {
                Log::warning('Groq tool_use_failed, mencoba ulang', ['body' => $response->body()]);
                $payload['temperature'] = 0.1;
                $payload['max_tokens'] = max((int) $payload['max_tokens'], 1200);
                if ($isGptOss) {
                    $payload['reasoning_effort'] = 'medium';
                }
                
                $apiKey = $this->getApiKey();
                $response = Http::timeout(30)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

                try {
                    GroqRateLimit::capture($response, $apiKey); // [FIX] Tambah $apiKey
                } catch (\Throwable $e) {
                    Log::warning('GroqRateLimit capture gagal (retry)', ['message' => $e->getMessage()]);
                }
            }

            if ($response->failed()) {
                Log::error('Groq API Error', ['status' => $response->status(), 'body' => $response->body()]);

                if ($response->status() === 429) {
                    Log::info('Rate limit hit, rotasi API Key dan coba lagi.');
                    $apiKey = $this->getApiKey();
                    
                    $response = Http::timeout(15)
                        ->withToken($apiKey)
                        ->acceptJson()
                        ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

                    if ($response->status() === 429) {
                        $wait = (int) ceil((float) $response->header('retry-after'));
                        if ($wait <= 0) {
                            $wait = self::RATE_LIMIT_COOLDOWN;
                        }
                        Cache::put('chatbot:groq:cooldown_until', time() + $wait, $wait + 5);

                        $reply = "AI sedang kena batas pemakaian. Coba lagi dalam {$wait} detik ya 🙏";
                        ChatHistory::log($sessionId, 'assistant', $reply, $userId);
                        return response()->json(['reply' => $reply, 'source' => 'rate_limited']);
                    }
                }

                if ($response->status() === 400) {
                    $reply = 'Maaf, AI gagal memproses permintaan itu. Coba ulangi dengan kalimat yang lebih singkat.';
                    ChatHistory::log($sessionId, 'assistant', $reply, $userId);
                    return response()->json(['reply' => $reply, 'source' => 'groq_error']);
                }

                if ($response->failed()) {
                    return response()->json([
                        'error' => 'Gagal menghubungi AI (HTTP '.$response->status().')',
                        'detail' => app()->isLocal() ? $response->json() : null,
                    ], 500);
                }
            }

            $responseData = $response->json() ?? [];
            $choice = $responseData['choices'][0] ?? null;

            if ($choice === null) {
                Log::warning('Groq balas tanpa choices', ['body' => $responseData]);
                $reply = 'Maaf, saya belum bisa memproses itu sekarang. Coba ulangi sebentar lagi ya.';
                ChatHistory::log($sessionId, 'assistant', $reply, $userId);
                return response()->json(['reply' => $reply, 'source' => 'groq']);
            }

            if (isset($choice['message']['tool_calls']) && count($choice['message']['tool_calls']) > 0) {
                return $this->handleToolCalls($choice['message'], $messages, $sessionId, $userId, $apiKey, $baseUrl, $model);
            }

            $reply = $this->cleanReply((string) ($choice['message']['content'] ?? ''));

            if ($reply === '') {
                Log::warning('Groq balas kosong', ['body' => $response->json()]);
                $reply = 'Maaf, saya belum bisa menjawab itu. Coba ulangi dengan kalimat lain.';
                ChatHistory::log($sessionId, 'assistant', $reply, $userId);
                return response()->json(['reply' => $reply, 'source' => 'groq']);
            }

            ChatHistory::log($sessionId, 'assistant', $reply, $userId);

            if (! $isAction && ! $isCasual) {
                Cache::put($cacheKey, $reply, $live ? self::CACHE_TTL_LIVE : self::CACHE_TTL_NORMAL);
            }

            return response()->json([
                'reply' => $reply,
                'source' => $live ? 'groq_search' : 'groq',
                'usage' => app()->isLocal() ? $response->json('usage') : null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Chatbot Exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Terjadi kesalahan: '.$e->getMessage()], 500);
        }
    }

    private function handleToolCalls(array $message, array $history, string $sessionId, ?int $userId, string $apiKey, string $baseUrl, string $model)
    {
        $toolCalls = $message['tool_calls'];

        $context = [];
        foreach ($history as $m) {
            if (in_array($m['role'] ?? '', ['user', 'assistant'], true) && ! empty($m['content']) && empty($m['tool_calls'])) {
                $context[] = ['role' => $m['role'], 'content' => mb_substr((string) $m['content'], 0, 600)];
            }
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->prompts->followUp()]],
            array_slice($context, -10)
        );

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
        $hasError = false;
        $errorMessage = '';

        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'];
            $arguments = json_decode($toolCall['function']['arguments'] ?? '', true);
            $arguments = is_array($arguments) ? $arguments : [];

            if (str_starts_with($functionName, 'create_')) {
                $dedupKey = 'chatbot:tool:dedup:'.$sessionId.':'.$functionName.':'.md5(json_encode($arguments));
                if (Cache::has($dedupKey)) {
                    Log::warning('[Chatbot] Duplicate create_ call blocked', [
                        'session' => $sessionId,
                        'tool' => $functionName,
                        'args' => $arguments,
                    ]);
                    $summaries[] = 'Aksi sudah diproses sebelumnya. JANGAN ulangi.';
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => json_encode(['success' => true, 'message' => 'Sudah diproses sebelumnya (tidak diulang).']),
                    ];
                    continue;
                }
                Cache::put($dedupKey, true, self::ACTION_DEDUP_TTL);
            }

            try {
                [$result, $summary] = $this->tools->run($functionName, $arguments, $user);
            } catch (\Throwable $e) {
                Log::error('[Chatbot] Tool exception', [
                    'tool' => $functionName,
                    'message' => $e->getMessage(),
                ]);
                $result = json_encode(['success' => false, 'error' => 'Terjadi kesalahan sistem saat menjalankan aksi.']);
                $summary = '⚠️ Gagal: '.$e->getMessage();
            }

            $resultArr = is_string($result) ? json_decode($result, true) : $result;
            $toolFailed = is_array($resultArr) && (
                ($resultArr['success'] ?? true) === false
                || isset($resultArr['error'])
            );

            if ($toolFailed) {
                $hasError = true;
                $errorMessage = (string) ($resultArr['error'] ?? ($resultArr['message'] ?? 'Aksi gagal diproses.'));
                Log::warning('[Chatbot] Tool execution failed', [
                    'tool' => $functionName,
                    'result' => $resultArr,
                ]);
                if (str_starts_with($functionName, 'create_')) {
                    $dedupKey = 'chatbot:tool:dedup:'.$sessionId.':'.$functionName.':'.md5(json_encode($arguments));
                    Cache::forget($dedupKey);
                }
            }

            $summaries[] = $summary;

            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'content' => $result,
            ];
        }

        if ($hasError) {
            $reply = '❌ Maaf, aksi gagal diproses: '.$errorMessage."\nSilakan coba lagi ya.";
            $reply = $this->cleanReply($reply);
            ChatHistory::log($sessionId, 'assistant', $reply, $userId);
            return response()->json(['reply' => $reply, 'source' => 'tool_error']);
        }

        return $this->askFollowUp($messages, $sessionId, $userId, $apiKey, $baseUrl, $model, $summaries);
    }

    private function askFollowUp(array $messages, string $sessionId, ?int $userId, string $apiKey, string $baseUrl, string $model, array $fallbackLines = [])
    {
        $apiKey = $this->getApiKey();

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

        $fallbackReply = $this->cleanReply($fallbackReply);

        try {
            $response = Http::timeout(20)
                ->retry(1, 300, fn ($exception) => $exception instanceof ConnectionException, throw: false)
                ->withToken($apiKey)
                ->acceptJson()
                ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

            try {
                GroqRateLimit::capture($response, $apiKey); // [FIX] Tambah $apiKey
            } catch (\Throwable $e) {
                Log::warning('GroqRateLimit capture gagal (follow up)', ['message' => $e->getMessage()]);
            }

            if ($response->status() === 429) {
                $apiKey = $this->getApiKey();
                $response = Http::timeout(20)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

                if ($response->status() === 429) {
                    $wait = (int) ceil((float) $response->header('retry-after'));
                    if ($wait <= 0) {
                        $wait = self::RATE_LIMIT_COOLDOWN;
                    }
                    Cache::put('chatbot:groq:cooldown_until', time() + $wait, $wait + 5);

                    ChatHistory::log($sessionId, 'assistant', $fallbackReply, $userId);
                    return response()->json(['reply' => $fallbackReply, 'source' => 'tool_fallback']);
                }
            }

            $data = $response->json() ?? [];

            if (isset($data['error']) || !isset($data['choices'][0]['message']['content'])) {
                Log::error('Groq follow up error', [
                    'status' => $response->status(),
                    'body' => $data ?: $response->body(),
                ]);
                ChatHistory::log($sessionId, 'assistant', $fallbackReply, $userId);
                return response()->json(['reply' => $fallbackReply, 'source' => 'tool_fallback']);
            }

            $reply = $this->cleanReply((string) $data['choices'][0]['message']['content']);

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

    private function cleanReply(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $text = preg_replace('/【[^】]*】/u', '', $text);
        $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text);
        $text = preg_replace('/\*(.+?)\*/s', '$1', $text);
        $text = preg_replace('/__(.+?)__/s', '$1', $text);
        $text = preg_replace('/(?<!\w)_(.+?)_(?!\w)/s', '$1', $text);
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);
        $text = preg_replace('/^[-*_]{3,}\s*$/m', '', $text);
        $text = preg_replace('/^[\*\+]\s+/m', '- ', $text);
        $text = preg_replace('/```[\s\S]*?```/', '', $text);
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
        $text = str_replace(['**', '__', '~~'], '', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);

        if (mb_strlen($text) > self::MAX_REPLY_CHARS) {
            $text = mb_substr($text, 0, self::MAX_REPLY_CHARS);
            $lastSpace = mb_strrpos($text, ' ');
            if ($lastSpace !== false && $lastSpace > self::MAX_REPLY_CHARS - 100) {
                $text = mb_substr($text, 0, $lastSpace);
            }
            $text = rtrim($text, " \t\n.,;:") . '…';
        }

        return trim($text);
    }
}