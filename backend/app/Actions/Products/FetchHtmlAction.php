<?php

namespace App\Actions\Products;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchHtmlAction
{
    /**
     * Fetch the raw HTML for a given URL with retry, proxy rotation & UA rotation.
     */
    public function __invoke(string $url, ?string $userAgent = null): string
    {
        $rotation = $this->proxyRotationPayload();

        $ua = $userAgent
            ?? $rotation['user_agent']
            ?? $this->randomConfiguredUserAgent();

        $proxy = $rotation['proxy'] ?? null;

        $request = $this->buildBaseRequest($ua, $proxy);

        return $this->fetchWithRetries($request, $url);
    }

    private function proxyRotationPayload(): array
    {
        $endpoint = config('scrape.proxy_rotation_url');

        if (! $endpoint) {
            return [];
        }

        try {
            return Http::timeout(3)->get($endpoint)->json() ?? [];
        } catch (\Throwable $e) {
            Log::debug('Proxy rotation failed; falling back to local list', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function randomConfiguredUserAgent(): string
    {
        $list = (array) config('scrape.user_agents', []);

        return $list ? ($list[array_rand($list)] ?? 'Mozilla/5.0') : 'Mozilla/5.0';
    }

    private function buildBaseRequest(string $ua, ?string $proxy): PendingRequest
    {
        $headers = [
            'User-Agent' => $ua,
            'Accept-Language' => 'en-US,en;q=0.9',
            'Cache-Control' => 'no-cache',
        ];

        $request = Http::withHeaders($headers);

        return $proxy
            ? $request->withOptions(['proxy' => $proxy])
            : $request;
    }

    private function fetchWithRetries(PendingRequest $request, string $url): string
    {
        $attempts = (int) config('scrape.retry_attempts', 1);
        $sleepMs = (int) config('scrape.retry_sleep_ms', 500);
        $timeout = (int) config('scrape.request_timeout', 10);
        $last = null;

        for ($i = 0; $i < $attempts; $i++) {
            try {
                $response = $request->timeout($timeout)->get($url);
                $response->throw();

                return $response->body();
            } catch (\Throwable $e) {
                $last = $e;
                $this->logRetry($e, $url, $i + 1, $attempts);
                $this->backoff($i, $attempts, $sleepMs);
            }
        }

        throw $last ?? new \RuntimeException('Failed to fetch HTML');
    }

    private function backoff(int $attemptIndex, int $totalAttempts, int $baseSleepMs): void
    {
        if ($attemptIndex >= $totalAttempts - 1) {
            return; // no sleep after last attempt
        }

        $sleep = $baseSleepMs * ($attemptIndex + 1);
        usleep($sleep * 1000);
    }

    private function logRetry(\Throwable $e, string $url, int $attempt, int $total): void
    {
        Log::debug('Fetch attempt failed', [
            'url' => $url,
            'attempt' => $attempt,
            'of' => $total,
            'error' => $e->getMessage(),
        ]);
    }
}
