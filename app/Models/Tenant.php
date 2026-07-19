<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'feature_overrides' => 'array',
        'feature_overrides_locked' => 'array',
        'lifecycle_events' => 'array',
        'trial_ends_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'last_active_at' => 'datetime',
        'suspended_at' => 'datetime',
        'churned_at' => 'datetime',
        'provisioned_at' => 'datetime',
        'provisioned' => 'boolean',
    ];

    public function domains()      { return $this->hasMany(TenantDomain::class); }
    public function plan()         { return $this->belongsTo(Plan::class); }
    public function subscriptions(){ return $this->hasMany(TenantSubscription::class); }
    public function invoices()     { return $this->hasMany(TenantInvoice::class); }

    public function getDatabaseName(): string { return $this->database_name ?? 'tenant_' . substr($this->id, 0, 8); }

    public function isTrialing(): bool { return $this->status === 'trial' && $this->trial_ends_at?->isFuture(); }
    public function isActive(): bool   { return $this->status === 'active'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }

    public function logEvent(string $event, array $payload = []): void
    {
        $events = (array) $this->lifecycle_events ?? [];
        $events[] = ['event' => $event, 'at' => now()->toIso8601String(), 'payload' => $payload];
        $this->lifecycle_events = array_slice($events, -100); // keep last 100
        $this->save();
    }
}
