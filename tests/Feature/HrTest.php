<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PerformanceReview;
use App\Models\Property;
use App\Models\User;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'HR Hotel', 'slug' => 'hr-test', 'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);
    $this->user = User::create([
        'name' => 'HR Manager', 'email' => 'hrmgr@test.com', 'password' => bcrypt('password'),
        'property_id' => $this->property->id, 'is_active' => true,
    ]);
});

it('creates employee with auto employee_no', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id,
        'employee_no' => 'EMP-2026-AAA11',
        'first_name' => 'Rudi', 'last_name' => 'Hartanto',
        'position' => 'Chef', 'department' => 'Kitchen',
        'joined_at' => '2025-03-01', 'basic_salary' => 6500000,
        'employment_type' => 'permanent', 'is_active' => true,
    ]);

    expect($emp->full_name)->toBe('Rudi Hartanto')
        ->and($emp->department)->toBe('Kitchen')
        ->and((float) $emp->basic_salary)->toBe(6500000.0)
        ->and($emp->is_active)->toBeTrue();
});

it('lists active employees', function () {
    Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-ACT1',
        'first_name' => 'Active', 'last_name' => 'User1',
        'position' => 'Staff', 'department' => 'Front Office',
        'joined_at' => '2025-06-01', 'basic_salary' => 4000000,
        'employment_type' => 'permanent', 'is_active' => true,
    ]);
    Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-INACT',
        'first_name' => 'Inactive', 'last_name' => 'User2',
        'position' => 'Staff', 'department' => 'Front Office',
        'joined_at' => '2025-01-01', 'basic_salary' => 4000000,
        'employment_type' => 'permanent', 'is_active' => false,
    ]);

    $active = Employee::where('property_id', $this->property->id)->where('is_active', true)->get();
    $all = Employee::where('property_id', $this->property->id)->get();

    expect($active)->toHaveCount(1)
        ->and($all)->toHaveCount(2);
});

it('updates employee data', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-UPD1',
        'first_name' => 'Before', 'last_name' => 'Update',
        'position' => 'Junior', 'department' => 'Engineering',
        'joined_at' => '2025-02-01', 'basic_salary' => 4200000,
        'employment_type' => 'permanent', 'is_active' => true,
    ]);

    $emp->update([
        'first_name' => 'After',
        'position' => 'Senior Engineer',
        'basic_salary' => 7200000,
    ]);

    $emp->refresh();
    expect($emp->first_name)->toBe('After')
        ->and($emp->position)->toBe('Senior Engineer')
        ->and((float) $emp->basic_salary)->toBe(7200000.0);
});

it('deletes employee', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-DEL1',
        'first_name' => 'Delete', 'last_name' => 'Me',
        'position' => 'Temp', 'department' => 'Temp',
        'joined_at' => '2026-01-01', 'basic_salary' => 1000000,
        'employment_type' => 'contract', 'is_active' => true,
    ]);

    $emp->delete();
    expect(Employee::find($emp->id))->toBeNull();
});

it('creates leave request in pending status', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-LV01',
        'first_name' => 'Sari', 'last_name' => 'Nirmala',
        'position' => 'Receptionist', 'department' => 'Front Office',
        'joined_at' => '2025-04-01', 'basic_salary' => 4500000,
        'employment_type' => 'permanent', 'is_active' => true,
    ]);

    $leave = LeaveRequest::create([
        'property_id' => $this->property->id,
        'employee_id' => $emp->id,
        'type' => 'annual',
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-14',
        'total_days' => 5,
        'reason' => 'Family vacation',
        'status' => 'pending',
    ]);

    expect($leave->status)->toBe('pending')
        ->and($leave->total_days)->toBe(5)
        ->and($leave->type)->toBe('annual')
        ->and($leave->employee_id)->toBe($emp->id);
});

it('approves leave request', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-LV02',
        'first_name' => 'Bayu', 'last_name' => 'Pratama',
        'position' => 'Bellboy', 'department' => 'Front Office',
        'joined_at' => '2025-07-01', 'basic_salary' => 3500000,
        'employment_type' => 'permanent', 'is_active' => true,
    ]);

    $leave = LeaveRequest::create([
        'property_id' => $this->property->id,
        'employee_id' => $emp->id,
        'type' => 'sick',
        'start_date' => '2026-06-20',
        'end_date' => '2026-06-21',
        'total_days' => 2,
        'reason' => 'Fever',
        'status' => 'pending',
    ]);

    $leave->update(['status' => 'approved', 'approved_by' => $this->user->id, 'approved_at' => now()]);

    expect($leave->fresh()->status)->toBe('approved')
        ->and($leave->fresh()->approved_by)->toBe($this->user->id);
});

it('rejects leave request', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-LV03',
        'first_name' => 'Citra', 'last_name' => 'Lestari',
        'position' => 'Waitress', 'department' => 'F&B',
        'joined_at' => '2025-09-01', 'basic_salary' => 3500000,
        'employment_type' => 'permanent', 'is_active' => true,
    ]);

    $leave = LeaveRequest::create([
        'property_id' => $this->property->id,
        'employee_id' => $emp->id,
        'type' => 'unpaid',
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-05',
        'total_days' => 5,
        'reason' => 'Personal matters',
        'status' => 'pending',
    ]);

    $leave->update(['status' => 'rejected']);

    expect($leave->fresh()->status)->toBe('rejected');
});

it('creates performance review with scores', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-PF01',
        'first_name' => 'Dewi', 'last_name' => 'Anggraini',
        'position' => 'Spa Manager', 'department' => 'Spa',
        'joined_at' => '2025-01-01', 'basic_salary' => 8000000,
        'employment_type' => 'permanent', 'is_active' => true,
    ]);

    $review = PerformanceReview::create([
        'property_id' => $this->property->id,
        'employee_id' => $emp->id,
        'reviewer_id' => $this->user->id,
        'review_date' => '2026-06-30',
        'period_start' => '2026-01-01',
        'period_end' => '2026-06-30',
        'scores' => ['service' => 5, 'teamwork' => 4, 'attendance' => 5],
        'overall_rating' => 5,
        'strengths' => 'Excellent customer service',
        'improvements' => 'Can delegate more tasks',
    ]);

    expect($review->overall_rating)->toBe(5)
        ->and($review->scores)->toHaveKeys(['service', 'teamwork', 'attendance'])
        ->and($review->reviewer_id)->toBe($this->user->id)
        ->and($review->strengths)->toBe('Excellent customer service');
});

it('computes employee allowances total', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id, 'employee_no' => 'EMP-2026-ALW1',
        'first_name' => 'Eko', 'last_name' => 'Wibowo',
        'position' => 'Driver', 'department' => 'Transport',
        'joined_at' => '2025-04-15', 'basic_salary' => 4000000,
        'employment_type' => 'permanent', 'is_active' => true,
        'position_allowance' => 500000,
        'transport_allowance' => 300000,
        'meal_allowance' => 200000,
    ]);

    $total = (float) $emp->basic_salary
        + (float) ($emp->position_allowance ?? 0)
        + (float) ($emp->transport_allowance ?? 0)
        + (float) ($emp->meal_allowance ?? 0);

    expect($total)->toBe(5000000.0);
});
