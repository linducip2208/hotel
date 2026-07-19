<?php

namespace App\Adapters\Contracts;

interface MailAdapterInterface extends AdapterInterface
{
    public function send(string $to, string $subject, string $html, array $options = []): array;
}
