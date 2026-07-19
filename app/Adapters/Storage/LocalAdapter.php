<?php

namespace App\Adapters\Storage;

use App\Adapters\Contracts\AdapterInterface;
use App\Models\Provider;

class LocalAdapter implements AdapterInterface
{
    public function __construct(protected Provider $provider) {}

    public function test(): array
    {
        return ['ok' => is_writable(storage_path('app')), 'message' => 'Local storage'];
    }
}
