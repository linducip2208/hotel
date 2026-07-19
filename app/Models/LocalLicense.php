<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalLicense extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
        'token_encrypted' => 'encrypted',
        'paired_at' => 'datetime',
        'last_heartbeat_attempt_at' => 'datetime',
        'last_heartbeat_success_at' => 'datetime',
        'grace_until' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public static function current(): ?self
    {
        return static::orderBy('id')->first();
    }

    public function isPaired(): bool
    {
        return in_array($this->status, ['paired', 'grace', 'degraded'], true);
    }

    public function feature(string $key, $default = false)
    {
        return data_get($this->features, $key, $default);
    }
}
