<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained('pos_outlets')->nullOnDelete();
            $table->string('waste_category')->default('prep'); // prep|spoilage|plate_return|overproduction|expired
            $table->string('food_name');
            $table->decimal('quantity_kg', 10, 3)->default(0);
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->foreignId('logged_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('logged_date');
            $table->string('meal_period')->default('lunch'); // breakfast|lunch|dinner|snack
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('food_waste_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('target_reduction_pct', 6, 2)->default(10);
            $table->decimal('baseline_kg', 10, 3)->default(0);
            $table->decimal('actual_kg', 10, 3)->default(0);
            $table->string('status')->default('active'); // active|completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_waste_targets');
        Schema::dropIfExists('food_waste_logs');
    }
};
