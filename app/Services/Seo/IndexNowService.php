<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    protected string $key;
    protected string $keyLocation;
    protected array $searchEngines = [
        'https://www.bing.com/indexnow',
        'https://yandex.com/indexnow',
        'https://search.seznam.cz/indexnow',
        'https://indexnow.naver.com/indexnow',
    ];

    public function __construct()
    {
        $this->key = (string) @file_get_contents(public_path('indexnow-key.txt'));
        $this->keyLocation = config('app.url') . '/indexnow-key.txt';
    }

    public function submit(array $urls): array
    {
        if (empty($urls)) return ['success' => false, 'message' => 'No URLs provided'];

        $payload = [
            'host' => parse_url(config('app.url'), PHP_URL_HOST),
            'key' => $this->key,
            'keyLocation' => $this->keyLocation,
            'urlList' => $urls,
        ];

        $results = [];
        foreach ($this->searchEngines as $engine) {
            try {
                $response = Http::timeout(15)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($engine, $payload);

                $results[$engine] = [
                    'status' => $response->status(),
                    'success' => $response->successful(),
                ];
            } catch (\Throwable $e) {
                $results[$engine] = ['status' => 0, 'error' => $e->getMessage()];
            }
        }

        Log::info('IndexNow: submitted ' . count($urls) . ' URLs', $results);

        return [
            'success' => true,
            'submitted' => count($urls),
            'engines' => $results,
        ];
    }

    public function submitAll(): array
    {
        $builder = new SitemapBuilder;
        $allUrls = [];

        foreach ($builder->index() as $group) {
            $urls = $builder->urlsForGroup($group);
            foreach (array_slice($urls, 0, 500) as $u) {
                $allUrls[] = $u['loc'];
            }
        }

        // IndexNow max 10,000 URLs per call. Chunk into batches.
        $chunks = array_chunk($allUrls, 5000);
        $total = 0;

        foreach ($chunks as $chunk) {
            $result = $this->submit($chunk);
            if ($result['success']) $total += count($chunk);
            sleep(1); // Rate limit
        }

        return ['success' => true, 'submitted' => $total];
    }

    public function submitSingle(string $url): array
    {
        return $this->submit([$url]);
    }

    public function submitNewOnly(array $urls): array
    {
        $lastSubmit = cache('indexnow_last_submit', []);
        $newUrls = array_diff($urls, $lastSubmit);

        if (empty($newUrls)) {
            return ['success' => true, 'submitted' => 0, 'message' => 'No new URLs'];
        }

        $result = $this->submit($newUrls);

        // Update cache with all submitted URLs (keep last 50,000)
        $allSubmitted = array_unique(array_merge($lastSubmit, $newUrls));
        if (count($allSubmitted) > 50000) {
            $allSubmitted = array_slice($allSubmitted, -50000);
        }
        cache(['indexnow_last_submit' => $allSubmitted], now()->addYear());

        return $result;
    }
}
