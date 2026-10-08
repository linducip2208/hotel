<?php

use App\Models\CancellationPolicy;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->property = Property::create([
        'name' => 'Http Hotel', 'slug' => 'http-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 5, 'is_active' => true,
    ]);
    $this->user = User::create([
        'name' => 'Http Admin', 'email' => 'http-admin@x.com',
        'password' => bcrypt('password-password'), 'property_id' => $this->property->id,
    ]);
    $this->user->assignRole('super_owner');
    $this->actingAs($this->user);
});

// ---------------------------------------------------------------------------
// JOURNAL — balanced posting via HTTP; unbalanced rejected with friendly error
// ---------------------------------------------------------------------------

it('posts a balanced journal entry via HTTP', function () {
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '1-1010', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '4-1010', 'name' => 'Pendapatan', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);

    $response = $this->post(route('panel.accounting.journal.store'), [
        'description' => 'Test entry',
        'lines' => [
            ['account_code' => '1-1010', 'debit' => 150000, 'credit' => null],
            ['account_code' => '4-1010', 'debit' => null, 'credit' => 150000],
        ],
    ]);

    $response->assertRedirect();
    expect(JournalEntry::where('property_id', $this->property->id)->count())->toBe(1);
});

it('rejects unbalanced journal with a validation message, not a 500', function () {
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '1-1010', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '4-1010', 'name' => 'Pendapatan', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);

    $response = $this->post(route('panel.accounting.journal.store'), [
        'description' => 'Bad entry',
        'lines' => [
            ['account_code' => '1-1010', 'debit' => 150000, 'credit' => null],
            ['account_code' => '4-1010', 'debit' => null, 'credit' => 100000],
        ],
    ]);

    $response->assertSessionHasErrors('lines');
    expect(JournalEntry::where('property_id', $this->property->id)->count())->toBe(0);
});

it('rejects COA from another property (cross-property journal attack)', function () {
    $other = Property::create(['name' => 'Other', 'slug' => 'other-http', 'region_code' => 'ID-YO', 'total_rooms' => 5, 'is_active' => true]);
    ChartOfAccount::create(['property_id' => $other->id, 'code' => '9-9999', 'name' => 'Other Kas', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '4-1010', 'name' => 'Pendapatan', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);

    $response = $this->post(route('panel.accounting.journal.store'), [
        'description' => 'Cross property',
        'lines' => [
            ['account_code' => '9-9999', 'debit' => 100000, 'credit' => null],
            ['account_code' => '4-1010', 'debit' => null, 'credit' => 100000],
        ],
    ]);

    $response->assertSessionHasErrors('lines');
    expect(JournalEntry::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// USER MANAGEMENT — create, role change, deactivate via HTTP
// ---------------------------------------------------------------------------

it('creates a staff user and changes role via HTTP with audit trail', function () {
    $this->post(route('panel.settings.users.store'), [
        'name' => 'Staff Baru',
        'email' => 'staff@x.com',
        'password' => 'password-rahasia',
        'role' => 'front_office',
    ])->assertSessionHasNoErrors();

    $staff = User::where('email', 'staff@x.com')->first();
    expect($staff)->not->toBeNull();
    expect($staff->property_id)->toBe($this->property->id);
    expect($staff->hasRole('front_office'))->toBeTrue();

    $this->patch(route('panel.settings.users.update', $staff->id), [
        'name' => 'Staff Baru II',
        'phone' => '0812',
        'role' => 'cashier',
    ])->assertSessionHasNoErrors();

    expect($staff->fresh()->hasRole('cashier'))->toBeTrue();
    expect($staff->fresh()->hasRole('front_office'))->toBeFalse();
});

it('deactivates a user and revokes sessions', function () {
    $staff = User::create([
        'name' => 'Nonaktif', 'email' => 'nonaktif@x.com',
        'password' => bcrypt('password-password'), 'property_id' => $this->property->id,
    ]);
    $staff->assignRole('cashier');

    $this->post(route('panel.settings.users.toggle-active', $staff->id))
        ->assertSessionHasNoErrors();

    expect($staff->fresh()->is_active)->toBeFalse();
});

it('prevents self-deactivation', function () {
    $this->post(route('panel.settings.users.toggle-active', $this->user->id))
        ->assertSessionHasErrors('users');

    expect($this->user->fresh()->is_active)->toBeTrue();
});

// ---------------------------------------------------------------------------
// CANCELLATION POLICY — full CRUD via HTTP
// ---------------------------------------------------------------------------

it('creates, updates, duplicates and deactivates a cancellation policy', function () {
    $this->post(route('panel.settings.cancellation-policies.store'), [
        'name' => 'Flexible',
        'is_refundable' => '1',
        'rules' => [
            ['days_before' => 14, 'penalty_pct' => 0],
            ['days_before' => 0, 'penalty_pct' => 100],
        ],
    ])->assertSessionHasNoErrors();

    $policy = CancellationPolicy::where('name', 'Flexible')->first();
    expect($policy)->not->toBeNull();
    expect($policy->is_active)->toBeTrue();

    $this->post(route('panel.settings.cancellation-policies.update', $policy->id), [
        'name' => 'Flexible v2',
        'rules' => [['days_before' => 7, 'penalty_pct' => 50]],
    ])->assertSessionHasNoErrors();
    expect($policy->fresh()->name)->toBe('Flexible v2');

    $this->post(route('panel.settings.cancellation-policies.duplicate', $policy->id))
        ->assertSessionHasNoErrors();
    expect(CancellationPolicy::where('name', 'like', 'Flexible v2%')->count())->toBe(2);

    $this->post(route('panel.settings.cancellation-policies.toggle', $policy->id))
        ->assertSessionHasNoErrors();
    expect($policy->fresh()->is_active)->toBeFalse();
});

it('rejects penalty above 100 percent', function () {
    $this->post(route('panel.settings.cancellation-policies.store'), [
        'name' => 'Invalid',
        'rules' => [['days_before' => 0, 'penalty_pct' => 150]],
    ])->assertSessionHasErrors();

    expect(CancellationPolicy::where('name', 'Invalid')->count())->toBe(0);
});

// ---------------------------------------------------------------------------
// ROLE dropdown — only valid roles for context
// ---------------------------------------------------------------------------

it('user create accepts only existing roles', function () {
    $this->post(route('panel.settings.users.store'), [
        'name' => 'Bad Role',
        'email' => 'badrole@x.com',
        'password' => 'password-rahasia',
        'role' => 'nonexistent_role',
    ])->assertSessionHasErrors('role');

    expect(User::where('email', 'badrole@x.com')->exists())->toBeFalse();
});
