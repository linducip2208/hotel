<?php

namespace App\Services\License;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class TokenVerifier
{
    public function verify(string $token): ?object
    {
        $publicKey = $this->loadPublicKey();
        if ($publicKey === null) {
            return null;
        }

        try {
            return JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (\Throwable $e) {
            Log::channel('license')->warning('License token verify failed: '.$e->getMessage());
            return null;
        }
    }

    public function publicKeyHashOk(): bool
    {
        $expected = config('license.public_key_sha256');
        if (! $expected) return true; // not set in env => skip integrity check (dev)

        $key = $this->loadPublicKey();
        if (! $key) return false;
        return hash_equals($expected, hash('sha256', $key));
    }

    protected function loadPublicKey(): ?string
    {
        $path = base_path(config('license.public_key_path'));
        if (! is_readable($path)) return null;
        $content = file_get_contents($path);
        if (! $content || str_contains($content, 'PLACEHOLDER')) {
            return null;
        }
        return $content;
    }
}
