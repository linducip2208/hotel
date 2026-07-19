<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rate_plans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('code'); // BAR, NRR, BB
            $t->string('name');
            $t->text('description')->nullable();
            $t->boolean('is_refundable')->default(true);
            $t->boolean('breakfast_included')->default(false);
            $t->json('cancellation_policy')->nullable();
            $t->boolean('is_derived')->default(false);
            $t->foreignId('parent_rate_plan_id')->nullable()->constrained('rate_plans')->nullOnDelete();
            $t->decimal('derive_modifier_pct', 6, 3)->nullable(); // e.g. +10.000 = +10%
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'code']);
        });

        Schema::create('rates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->decimal('amount', 12, 2);
            $t->string('currency', 3)->default('IDR');
            $t->unsignedSmallInteger('min_los')->default(1);
            $t->unsignedSmallInteger('max_los')->nullable();
            $t->boolean('cta')->default(false); // closed to arrival
            $t->boolean('ctd')->default(false); // closed to departure
            $t->boolean('closed')->default(false);
            $t->timestamps();
            $t->unique(['property_id', 'room_type_id', 'rate_plan_id', 'date']);
            $t->index(['property_id', 'date']);
        });

        Schema::create('inventory', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->unsignedSmallInteger('total')->default(0);
            $t->unsignedSmallInteger('sold')->default(0);
            $t->unsignedSmallInteger('blocked')->default(0);
            $t->unsignedSmallInteger('out_of_order')->default(0);
            $t->unsignedSmallInteger('overbooking_allowance')->default(0);
            $t->timestamps();
            $t->unique(['property_id', 'room_type_id', 'date']);
            $t->index(['property_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('rates');
        Schema::dropIfExists('rate_plans');
    }
};
