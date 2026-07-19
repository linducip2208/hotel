<?php

namespace App\Adapters\Contracts;

use App\Models\Provider;

interface AdapterInterface
{
    public function __construct(Provider $provider);

    /** Test connection. Return ['ok' => bool, 'message' => string]. */
    public function test(): array;
}
