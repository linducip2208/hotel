<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Provider extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'extra_headers' => 'array',
        'extra_config' => 'array',
        'capabilities' => 'array',
        'pricing' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = ['api_key_encrypted', 'secret_encrypted'];

    public function property() { return $this->belongsTo(Property::class); }

    public function setApiKey(?string $key): void
    {
        $this->api_key_encrypted = $key ? Crypt::encryptString($key) : null;
    }

    public function getApiKey(): ?string
    {
        return $this->api_key_encrypted ? Crypt::decryptString($this->api_key_encrypted) : null;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret_encrypted = $secret ? Crypt::encryptString($secret) : null;
    }

    public function getSecret(): ?string
    {
        return $this->secret_encrypted ? Crypt::decryptString($this->secret_encrypted) : null;
    }

    public function getMaskedKeyAttribute(): ?string
    {
        if (! $this->api_key_encrypted) return null;
        $plain = $this->getApiKey();
        if (strlen($plain) < 8) return str_repeat('*', strlen($plain));
        return substr($plain, 0, 4).str_repeat('*', max(4, strlen($plain) - 8)).substr($plain, -4);
    }
}
