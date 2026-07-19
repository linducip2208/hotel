<?php

namespace App\Adapters\Captcha;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\AdapterInterface;

class HcaptchaAdapter extends BaseAdapter implements AdapterInterface
{
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        $client = new \GuzzleHttp\Client(['timeout' => 5, 'http_errors' => false]);
        $r = $client->post('https://hcaptcha.com/siteverify', [
            'form_params' => [
                'secret' => $this->provider->getSecret(),
                'response' => $token,
                'remoteip' => $remoteIp,
            ],
        ]);
        $data = json_decode((string) $r->getBody(), true) ?? [];
        return (bool) ($data['success'] ?? false);
    }

    public function test(): array
    {
        return ['ok' => (bool) $this->provider->getSecret(), 'message' => 'Secret present'];
    }
}
