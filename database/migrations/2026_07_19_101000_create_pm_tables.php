<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pm_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $t->string('task_name');
            $t->string('frequency');
            $t->integer('interval_days')->nullable();
            $t->date('last_done_at')->nullable();
            $t->date('next_due_at')->nullable();
            $t->text('checklist')->nullable();
            $t->foreignId('assigned_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('pm_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('pm_schedule_id')->constrained()->cascadeOnDelete();
            $t->date('performed_at');
            $t->text('notes')->nullable();
            $t->json('checklist_results')->nullable();
            $t->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->decimal('cost', 14, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_logs');
        Schema::dropIfExists('pm_schedules');
    }
};
