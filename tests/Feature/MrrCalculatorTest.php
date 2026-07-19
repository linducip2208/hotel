<?php

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\Tenancy\MrrCalculator;

beforeEach(function () {
    $this->calc = app(MrrCalculator::class);
});

it('returns zero MRR when no active subscriptions', function () {
    expect($this->calc->totalMrr())->toBe(0.0)
        ->and($this->calc->arr())->toBe(0.0);
});

it('sums monthly subscriptions only', function () {
    $plan = Plan::create(['name' => 'Starter', 'slug' => 'starter-mrr', 'monthly_price_idr' => 1500000]);
    $t1 = Tenant::create(['id' => \Illuminate\Support\Str::uuid(), 'name' => 'T1', 'slug' => 'mrr-t1', 'company_name' => 'T1 Ltd', 'owner_name' => 'Owner1', 'subdomain' => 'mrr-t1', 'owner_email' => 't1@test.com', 'status' => 'active']);
    $t2 = Tenant::create(['id' => \Illuminate\Support\Str::uuid(), 'name' => 'T2', 'slug' => 'mrr-t2', 'company_name' => 'T2 Ltd', 'owner_name' => 'Owner2', 'subdomain' => 'mrr-t2', 'owner_email' => 't2@test.com', 'status' => 'active']);

    TenantSubscription::create(['tenant_id' => $t1->id, 'plan_id' => $plan->id, 'plan_name' => 'Starter', 'billing_cycle' => 'monthly', 'price_paid_idr' => 1500000, 'status' => 'active', 'started_at' => now(), 'current_period_start' => today(), 'current_period_end' => today()->addMonth()]);
    TenantSubscription::create(['tenant_id' => $t2->id, 'plan_id' => $plan->id, 'plan_name' => 'Pro', 'billing_cycle' => 'monthly', 'price_paid_idr' => 3000000, 'status' => 'active', 'started_at' => now(), 'current_period_start' => today(), 'current_period_end' => today()->addMonth()]);
    // annual should not count in MRR
    TenantSubscription::create(['tenant_id' => $t2->id, 'plan_id' => $plan->id, 'plan_name' => 'Pro Annual', 'billing_cycle' => 'annual', 'price_paid_idr' => 30000000, 'status' => 'active', 'started_at' => now(), 'current_period_start' => today(), 'current_period_end' => today()->addMonth()]);

    expect($this->calc->totalMrr())->toBe(4500000.0)
        ->and($this->calc->arr())->toBe(54000000.0);
});

it('churn rate is zero with no churned tenants', function () {
    Tenant::create(['id' => \Illuminate\Support\Str::uuid(), 'name' => 'Healthy', 'slug' => 'healthy', 'company_name' => 'Healthy Ltd', 'owner_name' => 'Owner', 'subdomain' => 'healthy', 'owner_email' => 'h@test.com', 'status' => 'active']);
    expect($this->calc->churnRatePct())->toBe(0.0);
});

it('calculates churn rate with churned tenant', function () {
    Tenant::create(['id' => \Illuminate\Support\Str::uuid(), 'name' => 'Active', 'slug' => 'churn-active', 'company_name' => 'Active Ltd', 'owner_name' => 'Owner', 'subdomain' => 'churn-active', 'owner_email' => 'ca@test.com', 'status' => 'active']);
    Tenant::create(['id' => \Illuminate\Support\Str::uuid(), 'name' => 'Gone', 'slug' => 'churn-gone', 'company_name' => 'Gone Ltd', 'owner_name' => 'Owner', 'subdomain' => 'churn-gone', 'owner_email' => 'cg@test.com', 'status' => 'churned', 'churned_at' => now()->subDays(5)]);

    expect($this->calc->churnRatePct())->toBe(50.0);
});
