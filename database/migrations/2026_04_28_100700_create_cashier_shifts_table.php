<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cashier_shifts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('cashier_id')->constrained('users');
            $t->timestamp('opened_at');
            $t->timestamp('closed_at')->nullable();
            $t->decimal('opening_float', 14, 2)->default(0);
            $t->decimal('expected_cash', 14, 2)->default(0);
            $t->decimal('actual_cash', 14, 2)->default(0);
            $t->decimal('cash_variance', 14, 2)->default(0);
            $t->json('breakdown')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'opened_at']);
        });

        Schema::create('night_audits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->date('audit_date')->index();
            $t->string('status')->default('pending'); // pending|running|completed|failed
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->foreignId('run_by_user_id')->nullable()->constrained('users');
            $t->json('summary')->nullable(); // occupancy, ADR, RevPAR, revenue breakdown
            $t->text('error_log')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'audit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_audits');
        Schema::dropIfExists('cashier_shifts');
    }
};
