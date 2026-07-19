<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gamification_badges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('icon')->default('star');
            $t->string('color')->default('amber');
            $t->string('category')->default('hk');
            $t->string('criteria');
            $t->integer('threshold')->default(10);
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('employee_points', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->integer('points');
            $t->string('reason');
            $t->string('category');
            $t->timestamp('earned_at')->useCurrent();
            $t->timestamps();
        });

        Schema::create('employee_badges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->foreignId('gamification_badge_id')->constrained()->cascadeOnDelete();
            $t->timestamp('awarded_at')->useCurrent();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_badges');
        Schema::dropIfExists('employee_points');
        Schema::dropIfExists('gamification_badges');
    }
};
