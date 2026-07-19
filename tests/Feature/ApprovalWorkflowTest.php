<?php

use App\Models\ApprovalRequest;
use App\Models\Property;
use App\Models\User;
use App\Services\Approvals\ApprovalService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Approval Hotel', 'slug' => 'approval-hotel', 'region_code' => 'ID-JI', 'total_rooms' => 10, 'is_active' => true,
    ]);
    app()->instance('current_property', $this->property);
    $this->svc = app(ApprovalService::class);
    $this->user = User::create(['name' => 'Staff', 'email' => 'staff@test.com', 'password' => bcrypt('password')]);
});

it('creates approval request in pending state', function () {
    $req = $this->svc->request('refund', ['amount' => 6000000, 'reason' => 'Guest complaint'], $this->user->id);

    expect($req->status)->toBe('pending')
        ->and($req->action_type)->toBe('refund')
        ->and($req->property_id)->toBe($this->property->id);
});

it('approves a pending request', function () {
    $req = $this->svc->request('comp_room', ['room_no' => '101'], $this->user->id);
    $approver = User::create(['name' => 'Manager', 'email' => 'mgr'.uniqid().'@test.com', 'password' => bcrypt('password')]);

    $this->svc->approve($req, $approver->id, 'Approved by manager');

    expect($req->fresh()->status)->toBe('approved')
        ->and($req->fresh()->approver_id)->toBe($approver->id)
        ->and($req->fresh()->approver_notes)->toBe('Approved by manager');
});

it('rejects a pending request', function () {
    $req = $this->svc->request('discount', ['pct' => 25], $this->user->id);
    $approver = User::create(['name' => 'Manager', 'email' => 'mgr'.uniqid().'@test.com', 'password' => bcrypt('password')]);

    $this->svc->reject($req, $approver->id, 'Exceeds policy');

    expect($req->fresh()->status)->toBe('rejected');
});

it('needsApproval returns true for discount above threshold', function () {
    expect($this->svc->needsApproval('discount', 25))->toBeTrue()
        ->and($this->svc->needsApproval('discount', 15))->toBeFalse();
});

it('needsApproval always requires approval for comp_room', function () {
    expect($this->svc->needsApproval('comp_room'))->toBeTrue();
});

it('needsApproval checks refund amount', function () {
    expect($this->svc->needsApproval('refund', 6000000))->toBeTrue()
        ->and($this->svc->needsApproval('refund', 4000000))->toBeFalse();
});
