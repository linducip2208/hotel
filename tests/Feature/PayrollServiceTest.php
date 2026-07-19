<?php

use App\Models\Employee;
use App\Models\Property;
use App\Services\Hr\PayrollService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'HR Hotel', 'slug' => 'hr-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 30, 'is_active' => true,
    ]);
    $this->svc = app(PayrollService::class);
});

it('generates payslip with correct BPJS and net salary', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id,
        'first_name' => 'Andi', 'last_name' => 'Santoso',
        'employee_no' => 'EMP-001', 'position' => 'Receptionist',
        'department' => 'Front Office', 'employment_type' => 'permanent',
        'marital_status' => 'single', 'dependents_count' => 0,
        'basic_salary' => 6000000,
        'position_allowance' => 500000, 'transport_allowance' => 300000, 'meal_allowance' => 200000,
        'is_active' => true, 'joined_at' => '2023-01-01',
    ]);

    $slip = $this->svc->generatePayslip($emp, 2026, 4);

    expect($slip->gross_total)->toEqual('7000000.00') // 6M + 1M allowances
        ->and((float) $slip->bpjs_kesehatan_employee)->toBe(70000.0)  // 1% of 7M
        ->and((float) $slip->bpjs_tk_employee)->toBe(210000.0)        // 3% of 7M
        ->and((float) $slip->net_salary)->toBeGreaterThan(6000000.0)
        ->and($slip->status)->toBe('draft');
});

it('applies PPh 21 for high earner', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id,
        'first_name' => 'Dirut', 'last_name' => 'Utama',
        'employee_no' => 'EMP-002', 'position' => 'General Manager',
        'department' => 'Management', 'employment_type' => 'permanent',
        'marital_status' => 'married', 'dependents_count' => 2,
        'basic_salary' => 50000000,
        'position_allowance' => 5000000,
        'transport_allowance' => 0, 'meal_allowance' => 0,
        'is_active' => true, 'joined_at' => '2020-01-01',
    ]);

    $slip = $this->svc->generatePayslip($emp, 2026, 4);

    expect((float) $slip->pph_21)->toBeGreaterThan(0.0)
        ->and((float) $slip->gross_total)->toBe(55000000.0);
});

it('computes overtime pay correctly', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id,
        'first_name' => 'Heru', 'last_name' => 'Purnomo',
        'employee_no' => 'EMP-003', 'position' => 'Housekeeping',
        'department' => 'HK', 'employment_type' => 'permanent',
        'marital_status' => 'single', 'dependents_count' => 0,
        'basic_salary' => 4000000,
        'position_allowance' => 0, 'transport_allowance' => 0, 'meal_allowance' => 0,
        'is_active' => true, 'joined_at' => '2024-06-01',
    ]);

    $slipNoOt = $this->svc->generatePayslip($emp, 2026, 3, 0, 0);
    $slipWithOt = $this->svc->generatePayslip($emp, 2026, 4, 0, 10);

    expect((float) $slipWithOt->overtime_pay)->toBeGreaterThan(0.0)
        ->and((float) $slipWithOt->gross_total)->toBeGreaterThan((float) $slipNoOt->gross_total);
});

it('upserts payslip on duplicate period', function () {
    $emp = Employee::create([
        'property_id' => $this->property->id,
        'first_name' => 'Wati', 'last_name' => 'R',
        'employee_no' => 'EMP-004', 'position' => 'Cashier',
        'department' => 'Finance', 'employment_type' => 'permanent',
        'marital_status' => 'single', 'dependents_count' => 0,
        'basic_salary' => 5000000,
        'position_allowance' => 0, 'transport_allowance' => 0, 'meal_allowance' => 0,
        'is_active' => true, 'joined_at' => '2025-01-01',
    ]);

    $this->svc->generatePayslip($emp, 2026, 4);
    $second = $this->svc->generatePayslip($emp, 2026, 4);

    expect($emp->payslips()->where('year', 2026)->where('month', 4)->count())->toBe(1)
        ->and($second->year)->toBe(2026);
});
