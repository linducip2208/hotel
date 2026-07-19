<?php

namespace App\Services\Integrations;

use App\Adapters\Contracts\AdapterInterface;
use App\Models\Provider;
use InvalidArgumentException;

class AdapterFactory
{
    public function make(Provider $provider): AdapterInterface
    {
        $map = config('integrations.'.$provider->integration_type.'.formats', []);
        $class = $map[$provider->api_format] ?? null;

        if (! $class || ! class_exists($class)) {
            throw new InvalidArgumentException("Unknown adapter for {$provider->integration_type}/{$provider->api_format}");
        }

        return new $class($provider);
    }
}
