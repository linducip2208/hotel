<?php

namespace App\Adapters\Sms;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\SmsAdapterInterface;

class SmppSmsAdapter extends BaseAdapter implements SmsAdapterInterface
{
    public function send(string $to, string $message): array
    {
        // SMPP umumnya butuh socket library terpisah; placeholder.
        return ['ok' => false, 'reason' => 'smpp_socket_not_implemented'];
    }

    public function test(): array
    {
        return ['ok' => false, 'message' => 'SMPP requires socket library — see docs.'];
    }
}
