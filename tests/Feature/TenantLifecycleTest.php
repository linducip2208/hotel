<?php

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Tenancy\MrrCalculator;
use App\Services\Tenancy\TenantLifecycleService;

it('detects trialing tenant as trialing', function () {
    $t = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Trial Co', 'slug' => 'trial-co', 'company_name' => 'Trial Co Ltd', 'owner_name' => 'Owner', 'subdomain' => 'trial-co',
        'owner_email' => 'owner@trial.com', 'status' => 'trial',
        'trial_ends_at' => now()->addDays(5),
    ]);

    expect($t->isTrialing())->toBeTrue()
        ->and($t->isActive())->toBeFalse();
});

it('transitions trial → past_due when trial_ends_at is past', function () {
    $t = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Expired Co', 'slug' => 'expired-co', 'company_name' => 'Expired Co Ltd', 'owner_name' => 'Owner', 'subdomain' => 'expired-co',
        'owner_email' => 'expired@test.com', 'status' => 'trial',
        'trial_ends_at' => now()->subDays(1),
    ]);

    $svc = app(TenantLifecycleService::class);
    $stats = $svc->processAll();

    expect($stats['trial_expired'])->toBe(1)
        ->and($t->fresh()->status)->toBe('past_due');
});

it('transitions past_due → suspended after grace period', function () {
    $t = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Grace Co', 'slug' => 'grace-co', 'company_name' => 'Grace Co Ltd', 'owner_name' => 'Owner', 'subdomain' => 'grace-co',
        'owner_email' => 'grace@test.com', 'status' => 'past_due',
        'trial_ends_at' => now()->subDays(TenantLifecycleService::SUSPEND_GRACE_DAYS + 1),
    ]);

    $svc = app(TenantLifecycleService::class);
    $svc->processAll();

    expect($t->fresh()->status)->toBe('suspended')
        ->and($t->fresh()->suspended_at)->not->toBeNull();
});

it('transitions suspended → churned after 90 days', function () {
    $t = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Old Co', 'slug' => 'old-co', 'company_name' => 'Old Co Ltd', 'owner_name' => 'Owner', 'subdomain' => 'old-co',
        'owner_email' => 'old@test.com', 'status' => 'suspended',
        'trial_ends_at' => now()->subDays(100),
        'suspended_at' => now()->subDays(TenantLifecycleService::HARD_DELETE_DAYS + 1),
    ]);

    $svc = app(TenantLifecycleService::class);
    $svc->processAll();

    expect($t->fresh()->status)->toBe('churned')
        ->and($t->fresh()->churned_at)->not->toBeNull();
});

it('logEvent appends and limits to 100 entries', function () {
    $t = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Log Co', 'slug' => 'log-co', 'company_name' => 'Log Co Ltd', 'owner_name' => 'Owner', 'subdomain' => 'log-co',
        'owner_email' => 'log@test.com', 'status' => 'active',
    ]);

    for ($i = 0; $i < 105; $i++) {
        $t->logEvent('ping', ['i' => $i]);
    }

    expect(count($t->fresh()->lifecycle_events))->toBe(100);
});

it('mrr calculator returns correct totals', function () {
    $t = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Active Co', 'slug' => 'active-co', 'company_name' => 'Active Co Ltd', 'owner_name' => 'Owner', 'subdomain' => 'active-co',
        'owner_email' => 'active@test.com', 'status' => 'active',
    ]);
    $plan = Plan::create(['name' => 'Pro', 'slug' => 'pro-lifecycle']);
    \App\Models\TenantSubscription::create([
        'tenant_id' => $t->id, 'plan_id' => $plan->id, 'plan_name' => 'Pro',
        'billing_cycle' => 'monthly', 'price_paid_idr' => 2500000,
        'status' => 'active', 'started_at' => now(), 'current_period_start' => today(), 'current_period_end' => today()->addMonth(),
    ]);

    $calc = app(MrrCalculator::class);
    expect($calc->totalMrr())->toBe(2500000.0)
        ->and($calc->arr())->toBe(30000000.0)
        ->and($calc->activeTenantsCount())->toBe(1);
});
