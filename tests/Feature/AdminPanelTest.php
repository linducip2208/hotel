<?php

use App\Http\Controllers\Admin\SystemController;
use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.default' => 'sqlite']);

    $this->admin = AdminUser::create([
        'email' => 'root@saas.test',
        'password' => bcrypt('super-secret-password'),
        'name' => 'SaaS Admin',
        'role' => 'super_admin',
        'is_active' => true,
    ]);
});

it('renders billing pages with real data', function () {
    $pages = [
        route('admin.billing.index'),
        route('admin.billing.subscriptions'),
        route('admin.billing.invoices'),
        route('admin.billing.coupons'),
        route('admin.billing.failed'),
    ];

    foreach ($pages as $url) {
        $this->actingAs($this->admin, 'admin')->get($url)->assertOk();
    }
});

it('renders system pages including feature flags', function () {
    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.system.flags'))
        ->assertOk()
        ->assertSee('channel_manager', false)
        ->assertSee('Simpan Flags', false);

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.system.email-templates'))
        ->assertOk();
});

it('feature flag overrides persist and merge over config', function () {
    $this->actingAs($this->admin, 'admin')
        ->patch(route('admin.system.flags.update'), [
            'flags' => ['banquet' => '1', 'spa' => '1'],
        ]);

    $overrides = SystemController::overrides();
    expect($overrides['banquet'] ?? null)->toBeTrue();
    expect($overrides['spa'] ?? null)->toBeTrue();

    @unlink(storage_path('app/private/feature-flags.json'));
});

it('renders support pages honestly (kb real, tickets empty state)', function () {
    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.support.kb'))
        ->assertOk();

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.support.tickets'))
        ->assertOk()
        ->assertSee('instalasi ini', false);
});

it('license create form is functional, not a coming-soon stub', function () {
    $response = $this->actingAs($this->admin, 'admin')
        ->get(route('admin.licenses.create'));

    $response->assertOk();
    expect($response->getContent())->not->toContain('coming soon');
});

it('no admin page shows a coming-soon stub anymore', function () {
    $urls = [
        route('admin.billing.index'),
        route('admin.billing.subscriptions'),
        route('admin.billing.invoices'),
        route('admin.billing.coupons'),
        route('admin.billing.failed'),
        route('admin.system.flags'),
        route('admin.system.email-templates'),
        route('admin.support.tickets'),
        route('admin.support.kb'),
        route('admin.licenses.create'),
    ];

    foreach ($urls as $url) {
        $response = $this->actingAs($this->admin, 'admin')->get($url);
        expect(strtolower($response->getContent()))->not->toContain('coming soon');
    }
});
