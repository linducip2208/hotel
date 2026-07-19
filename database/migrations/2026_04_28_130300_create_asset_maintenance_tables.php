<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('asset_no')->unique();
            $t->string('name');
            $t->string('category');
            $t->foreignId('room_id')->nullable()->constrained();
            $t->string('serial_no')->nullable();
            $t->string('vendor')->nullable();
            $t->date('purchased_at')->nullable();
            $t->decimal('purchase_cost', 14, 2)->nullable();
            $t->unsignedSmallInteger('useful_life_years')->nullable();
            $t->decimal('residual_value', 14, 2)->default(0);
            $t->decimal('accumulated_depreciation', 14, 2)->default(0);
            $t->string('depreciation_method')->default('straight_line');
            $t->string('status')->default('active'); // active|maintenance|disposed
            $t->date('disposed_at')->nullable();
            $t->text('notes')->nullable();
            $t->json('photos')->nullable();
            $t->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('wo_no')->unique();
            $t->foreignId('asset_id')->nullable()->constrained();
            $t->foreignId('room_id')->nullable()->constrained();
            $t->string('type'); // corrective|preventive|inspection
            $t->string('priority')->default('normal');
            $t->text('description');
            $t->foreignId('assignee_id')->nullable()->constrained('users');
            $t->string('status')->default('open'); // open|in_progress|done|verified|cancelled
            $t->timestamp('reported_at')->useCurrent();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->decimal('cost_material', 14, 2)->default(0);
            $t->decimal('cost_labor', 14, 2)->default(0);
            $t->json('material_used')->nullable();
            $t->json('photos_before')->nullable();
            $t->json('photos_after')->nullable();
            $t->text('resolution')->nullable();
            $t->timestamps();
        });

        Schema::create('preventive_maintenance_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->constrained();
            $t->string('frequency'); // daily|weekly|monthly|quarterly|yearly
            $t->date('next_due_at');
            $t->date('last_done_at')->nullable();
            $t->text('checklist')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_schedules');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('assets');
    }
};
