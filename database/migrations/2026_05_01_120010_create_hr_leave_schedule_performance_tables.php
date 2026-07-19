<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->string('type'); // annual|sick|maternity|paternity|unpaid
            $t->date('start_date');
            $t->date('end_date');
            $t->unsignedTinyInteger('total_days');
            $t->text('reason')->nullable();
            $t->string('status')->default('pending'); // pending|approved|rejected|cancelled
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->unsignedSmallInteger('year');
            $t->unsignedTinyInteger('total_annual')->default(12);
            $t->unsignedTinyInteger('used_annual')->default(0);
            $t->unsignedTinyInteger('total_sick')->default(12);
            $t->unsignedTinyInteger('used_sick')->default(0);
            $t->timestamps();
            $t->unique(['employee_id', 'year']);
        });

        Schema::create('shift_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->date('date');
            $t->string('shift_type'); // morning|afternoon|night|off
            $t->string('department')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['employee_id', 'date']);
        });

        Schema::create('performance_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->foreignId('reviewer_id')->constrained('users');
            $t->date('review_date');
            $t->date('period_start');
            $t->date('period_end');
            $t->json('scores'); // {attendance, punctuality, quality, teamwork, leadership, etc}
            $t->text('strengths')->nullable();
            $t->text('improvements')->nullable();
            $t->unsignedTinyInteger('overall_rating')->nullable(); // 1-5
            $t->json('goals')->nullable();
            $t->string('status')->default('draft'); // draft|completed|acknowledged
            $t->timestamp('acknowledged_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
    }
};
