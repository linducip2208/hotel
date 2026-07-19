<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linen_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type')->default('other');
            $t->unsignedInteger('par_level')->default(0);
            $t->unsignedInteger('current_stock')->default(0);
            $t->unsignedInteger('damaged_count')->default(0);
            $t->timestamps();
        });

        Schema::create('laundry_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('linen_category_id')->constrained('linen_categories')->cascadeOnDelete();
            $t->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $t->string('transaction_type');
            $t->unsignedInteger('quantity');
            $t->string('location_from')->nullable();
            $t->string('location_to')->nullable();
            $t->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('uniform_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->foreignId('linen_category_id')->constrained('linen_categories')->cascadeOnDelete();
            $t->unsignedInteger('quantity_assigned')->default(1);
            $t->date('assigned_date');
            $t->date('returned_date')->nullable();
            $t->string('condition')->default('baik');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniform_assignments');
        Schema::dropIfExists('laundry_transactions');
        Schema::dropIfExists('linen_categories');
    }
};
