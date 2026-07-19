<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spa_memberships', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->constrained();
            $t->string('membership_number')->unique();
            $t->string('plan_type'); // monthly|quarterly|annual
            $t->date('start_date');
            $t->date('end_date');
            $t->string('status')->default('active'); // active|expired|cancelled
            $t->boolean('auto_renew')->default(false);
            $t->decimal('price', 14, 2);
            $t->string('payment_method')->default('cash');
            $t->timestamps();
        });

        Schema::create('spa_membership_usages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('membership_id')->constrained('spa_memberships')->cascadeOnDelete();
            $t->foreignId('spa_appointment_id')->constrained()->cascadeOnDelete();
            $t->decimal('discount_amount', 14, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spa_membership_usages');
        Schema::dropIfExists('spa_memberships');
    }
};
