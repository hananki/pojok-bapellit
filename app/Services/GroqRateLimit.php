<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqRateLimit
{
    /**
     * Simpan info rate limit dari response Groq berdasarkan API Key.
     */
    public static function capture(Response $response, string $apiKey): void
    {
        $keyId = substr(md5($apiKey), 0, 8);
        
        $get = function (array $names) use ($response): ?string {
            foreach ($names as $name) {
                $val = $response->header($name);
                if (! empty($val)) {
                    return $val;
                }
            }
            return null;
        };

        $requestsLimit = $get(['x-ratelimit-limit-requests', 'x-ratelimit-limit-requests-day', 'x-ratelimit-limit-requests-minute']);
        $requestsRemaining = $get(['x-ratelimit-remaining-requests', 'x-ratelimit-remaining-requests-day', 'x-ratelimit-remaining-requests-minute']);
        $requestsReset = $get(['x-ratelimit-reset-requests', 'x-ratelimit-reset-requests-day', 'x-ratelimit-reset-requests-minute']);

        $tokensLimit = $get(['x-ratelimit-limit-tokens', 'x-ratelimit-limit-tokens-minute']);
        $tokensRemaining = $get(['x-ratelimit-remaining-tokens', 'x-ratelimit-remaining-tokens-minute']);
        $tokensReset = $get(['x-ratelimit-reset-tokens', 'x-ratelimit-reset-tokens-minute']);

        $retryAfter = $get(['retry-after']);

        $data = [
            'requests_limit' => (int) $requestsLimit,
            'requests_remaining' => (int) $requestsRemaining,
            'requests_reset' => $requestsReset,
            'tokens_limit' => (int) $tokensLimit,
            'tokens_remaining' => (int) $tokensRemaining,
            'tokens_reset' => $tokensReset,
            'retry_after' => $retryAfter,
            'header_available' => ! empty($requestsLimit) || ! empty($tokensLimit),
            'http_status' => $response->status(),
            'captured_at' => now()->toIso8601String(),
        ];

        Cache::put('groq_rate_limit_' . $keyId, $data, now()->addHours(24));
    }

    /**
     * Cek limit langsung ke semua API Key (Live Fetch)
     */
    public static function formatForChat(): string
    {
        $keys = config('services.groq.keys', []);
        if (empty($keys)) {
            return "Belum ada API Key yang di-set.";
        }

        $baseUrl = config('services.groq.url', 'https://api.groq.com/openai/v1');
        $model = config('services.groq.model', 'openai/gpt-oss-20b');

        $totalReqLimit = 0;
        $totalReqRemaining = 0;
        $totalTokLimit = 0;
        $totalTokRemaining = 0;
        $keyAktif = 0;
        
        foreach ($keys as $index => $apiKey) {
            $keyAktif++;
            try {
                // Nembak request sekecil mungkin (1 token) buat dapet header limit
                $response = Http::timeout(5)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->post(rtrim($baseUrl, '/').'/chat/completions', [
                        'model' => $model,
                        'messages' => [['role' => 'user', 'content' => 'hi']],
                        'max_tokens' => 1,
                    ]);

                $reqLimit = $response->header('x-ratelimit-limit-requests') ?? 0;
                $reqRemaining = $response->header('x-ratelimit-remaining-requests') ?? 0;
                $tokLimit = $response->header('x-ratelimit-limit-tokens') ?? 0;
                $tokRemaining = $response->header('x-ratelimit-remaining-tokens') ?? 0;

                $totalReqLimit += (int) $reqLimit;
                $totalReqRemaining += (int) $reqRemaining;
                $totalTokLimit += (int) $tokLimit;
                $totalTokRemaining += (int) $tokRemaining;

                // Simpan ke cache juga biar ada history
                self::capture($response, $apiKey);
                
            } catch (\Throwable $e) {
                Log::warning("Gagal cek limit API Key ke-" . $keyAktif, ['message' => $e->getMessage()]);
            }
        }

        if ($totalReqLimit === 0) {
            return " *Info Limit Groq*\n\n"
                 ."Gagal membaca header rate limit dari API Key. Pastikan API Key valid.";
        }

        $reqPercent = round(($totalReqRemaining / $totalReqLimit) * 100);
        $tokPercent = round(($totalTokRemaining / $totalTokLimit) * 100);

        $out = " *Info Limit Groq (Semua API Key)*\n\n";
        $out .= "Total API Key dicek: {$keyAktif}\n\n";

        $out .= "🔹 *Request*\n";
        $out .= "   Sisa: *{$totalReqRemaining}* / {$totalReqLimit} ({$reqPercent}%)\n\n";

        $out .= "🔹 *Token*\n";
        $out .= "   Sisa: *{$totalTokRemaining}* / {$totalTokLimit} ({$tokPercent}%)\n\n";

        $out .= " Data realtime.";

        return $out;
    }
}