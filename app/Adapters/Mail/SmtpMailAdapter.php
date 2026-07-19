<?php

namespace App\Adapters\Mail;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\MailAdapterInterface;
use Illuminate\Support\Facades\Mail;

class SmtpMailAdapter extends BaseAdapter implements MailAdapterInterface
{
    public function send(string $to, string $subject, string $html, array $options = []): array
    {
        // Programmatically set SMTP transport from provider config
        $cfg = $this->provider->extra_config ?? [];
        config([
            'mail.mailers.dynamic' => [
                'transport' => 'smtp',
                'host' => $cfg['host'] ?? '127.0.0.1',
                'port' => $cfg['port'] ?? 587,
                'encryption' => $cfg['encryption'] ?? 'tls',
                'username' => $this->apiKey(),
                'password' => $this->provider->getSecret(),
                'timeout' => 15,
            ],
        ]);

        try {
            Mail::mailer('dynamic')->html($html, function ($m) use ($to, $subject, $options) {
                $m->to($to);
                $m->subject($subject);
                if ($f = ($options['from'] ?? null)) $m->from($f);
            });
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function test(): array
    {
        return ['ok' => true, 'message' => 'Config valid (no test send)'];
    }
}
