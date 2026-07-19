<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained();
            $t->string('employee_no')->unique();
            $t->string('first_name');
            $t->string('last_name')->nullable();
            $t->string('nik')->nullable();
            $t->date('date_of_birth')->nullable();
            $t->string('gender', 1)->nullable();
            $t->string('marital_status')->nullable();
            $t->unsignedTinyInteger('dependents_count')->default(0);
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('address')->nullable();
            $t->string('npwp')->nullable();
            $t->string('bpjs_kesehatan')->nullable();
            $t->string('bpjs_tk')->nullable();
            $t->string('bank_account')->nullable();
            $t->string('bank_name')->nullable();
            $t->string('position');
            $t->string('department');
            $t->date('joined_at');
            $t->date('terminated_at')->nullable();
            $t->decimal('basic_salary', 14, 2);
            $t->decimal('position_allowance', 14, 2)->default(0);
            $t->decimal('transport_allowance', 14, 2)->default(0);
            $t->decimal('meal_allowance', 14, 2)->default(0);
            $t->json('other_allowances')->nullable();
            $t->string('employment_type')->default('permanent'); // permanent|contract|daily|outsource
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('attendance_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->date('date')->index();
            $t->dateTime('clock_in')->nullable();
            $t->dateTime('clock_out')->nullable();
            $t->string('status')->default('present'); // present|absent|sick|leave|holiday|late
            $t->unsignedSmallInteger('overtime_minutes')->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['employee_id', 'date']);
        });

        Schema::create('payslips', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained();
            $t->unsignedSmallInteger('year');
            $t->unsignedTinyInteger('month');
            $t->decimal('basic_salary', 14, 2);
            $t->decimal('allowances_total', 14, 2);
            $t->decimal('overtime_pay', 14, 2)->default(0);
            $t->decimal('service_charge', 14, 2)->default(0);
            $t->decimal('gross_total', 14, 2);
            $t->decimal('bpjs_kesehatan_employee', 14, 2)->default(0);
            $t->decimal('bpjs_tk_employee', 14, 2)->default(0);
            $t->decimal('pph_21', 14, 2)->default(0);
            $t->decimal('deductions_total', 14, 2);
            $t->decimal('net_salary', 14, 2);
            $t->json('breakdown')->nullable();
            $t->string('status')->default('draft'); // draft|approved|paid
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
            $t->unique(['employee_id', 'year', 'month']);
        });

        Schema::create('service_charge_distributions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('year');
            $t->unsignedTinyInteger('month');
            $t->decimal('total_collected', 14, 2);
            $t->decimal('admin_share_pct', 6, 3)->default(0);
            $t->decimal('staff_share_amount', 14, 2);
            $t->string('status')->default('pending'); // pending|distributed
            $t->timestamp('distributed_at')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_charge_distributions');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('employees');
    }
};
