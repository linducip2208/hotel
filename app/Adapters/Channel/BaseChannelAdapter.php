<?php

namespace App\Adapters\Channel;

use App\Models\Channel;

abstract class BaseChannelAdapter
{
    public function __construct(protected Channel $channel) {}

    abstract public function pushAvailability(array $updates): array;
    abstract public function pushRates(array $updates): array;
    abstract public function pushRestrictions(array $updates): array;
    abstract public function fetchBookings(?\DateTimeInterface $since = null): array;
    abstract public function test(): array;

    protected function getBaseUrl(): string
    {
        $provider = $this->channel->provider;
        if ($provider?->base_url) {
            return rtrim($provider->base_url, '/') . '/';
        }

        $chBase = $this->channel->config['base_url'] ?? $this->channel->settings['base_url'] ?? null;
        if ($chBase) {
            return rtrim($chBase, '/') . '/';
        }

        return $this->defaultBaseUrl();
    }

    abstract protected function defaultBaseUrl(): string;
}
