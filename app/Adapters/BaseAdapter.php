<?php

namespace App\Adapters;

use App\Adapters\Contracts\AdapterInterface;
use App\Models\Provider;
use GuzzleHttp\Client;

abstract class BaseAdapter implements AdapterInterface
{
    protected Client $http;

    public function __construct(protected Provider $provider)
    {
        $this->http = new Client([
            'base_uri' => $this->baseUrl(),
            'timeout' => $this->timeout(),
            'connect_timeout' => 5,
            'http_errors' => false,
            'headers' => $this->defaultHeaders(),
        ]);
    }

    protected function baseUrl(): string
    {
        return rtrim((string) $this->provider->base_url, '/').'/';
    }

    protected function timeout(): int
    {
        return 30;
    }

    protected function apiKey(): ?string
    {
        return $this->provider->getApiKey();
    }

    protected function defaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        if ($extra = $this->provider->extra_headers) {
            foreach ((array) $extra as $k => $v) {
                $headers[$k] = $v;
            }
        }
        return $headers;
    }
}
