<?php

namespace App\Adapters\Storage;

use App\Adapters\Contracts\AdapterInterface;
use App\Models\Provider;
use Aws\S3\S3Client;

class S3CompatibleAdapter implements AdapterInterface
{
    protected S3Client $client;

    public function __construct(protected Provider $provider)
    {
        $cfg = $provider->extra_config ?? [];
        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $cfg['region'] ?? 'auto',
            'endpoint' => $provider->base_url,
            'use_path_style_endpoint' => $cfg['path_style'] ?? true,
            'credentials' => [
                'key' => $provider->getApiKey(),
                'secret' => $provider->getSecret(),
            ],
        ]);
    }

    public function test(): array
    {
        $bucket = data_get($this->provider->extra_config, 'bucket');
        try {
            $this->client->headBucket(['Bucket' => $bucket]);
            return ['ok' => true, 'message' => 'Bucket reachable'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function client(): S3Client { return $this->client; }
}
