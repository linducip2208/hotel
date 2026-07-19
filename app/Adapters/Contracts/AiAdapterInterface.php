<?php

namespace App\Adapters\Contracts;

interface AiAdapterInterface extends AdapterInterface
{
    public function chat(array $messages, ?string $model = null, array $options = []): array;
    public function listModels(): array;
}
