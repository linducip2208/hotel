<?php

namespace App\Adapters\Contracts;

interface WhatsappAdapterInterface extends AdapterInterface
{
    public function send(string $to, string $template, array $variables = []): array;
}
