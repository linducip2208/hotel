<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Open Pricing Engine: each room_type × channel × date combination can have
 * an independent price override, bypassing the static BAR / derived-rate model.
 * Dynamic pricing rules define threshold-triggered adjustments applied nightly.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('rate_overrides', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('channel_id')->nullable()->constrained()->nullOnDelete();
            $t->date('override_date');
            $t->decimal('price', 14, 2);
            $t->decimal('min_price', 14, 2)->nullable();
            $t->decimal('max_price', 14, 2)->nullable();
            $t->unsignedTinyInteger('min_stay')->default(1);
            $t->unsignedTinyInteger('max_stay')->nullable();
            $t->boolean('closed_to_arrival')->default(false);
            $t->boolean('closed_to_departure')->default(false);
            $t->boolean('stop_sell')->default(false);
            $t->string('source')->default('manual'); // manual|dynamic|import
            $t->foreignId('created_by_user_id')->nullable()->constrained('users');
            $t->timestamps();
            // One override per room_type × channel × date
            $t->unique(['property_id', 'room_type_id', 'channel_id', 'override_date'], 'ro_rt_ch_date_unique');
            $t->index(['property_id', 'override_date']);
        });

        Schema::create('dynamic_pricing_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('channel_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('trigger_metric'); // occupancy_pct|days_to_arrival|pickup_pace|competitor_rate
            $t->string('operator');       // gte|lte|between
            $t->decimal('threshold_low', 8, 2);
            $t->decimal('threshold_high', 8, 2)->nullable();
            $t->string('action');         // pct_increase|pct_decrease|fixed_increase|fixed_decrease|stop_sell
            $t->decimal('action_value', 8, 2);
            $t->decimal('min_price_floor', 14, 2)->nullable();
            $t->decimal('max_price_ceiling', 14, 2)->nullable();
            $t->unsignedTinyInteger('lookahead_days')->default(30);
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_applied_at')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'is_active']);
        });

        Schema::create('dynamic_pricing_log', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rule_id')->nullable()->constrained('dynamic_pricing_rules')->nullOnDelete();
            $t->date('target_date');
            $t->foreignId('room_type_id')->constrained();
            $t->foreignId('channel_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('price_before', 14, 2);
            $t->decimal('price_after', 14, 2);
            $t->string('trigger_reason');
            $t->json('metrics_snapshot');
            $t->timestamps();
            $t->index(['property_id', 'target_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_pricing_log');
        Schema::dropIfExists('dynamic_pricing_rules');
        Schema::dropIfExists('rate_overrides');
    }
};
