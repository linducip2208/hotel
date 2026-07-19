<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function auditable() { return $this->morphTo(); }
    public function property()  { return $this->belongsTo(Property::class); }
    public function user()      { return $this->belongsTo(User::class, 'user_id'); }

    /**
     * Compute hash for this entry, optionally chained to previous.
     * Hash input: stable JSON of relevant fields. Tampering breaks chain.
     */
    public function computeHash(?string $previousHash = null): string
    {
        $payload = [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'before' => $this->before,
            'after' => $this->after,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'previous_hash' => $previousHash,
        ];
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function verifyHash(): bool
    {
        return hash_equals($this->entry_hash ?? '', $this->computeHash($this->previous_hash));
    }
}
