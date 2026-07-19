<?php

namespace App\Adapters\Contracts;

interface SmsAdapterInterface extends AdapterInterface
{
    public function send(string $to, string $message): array;
}
