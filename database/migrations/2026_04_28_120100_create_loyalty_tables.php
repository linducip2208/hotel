<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name'); // Silver, Gold, Platinum
            $t->string('slug');
            $t->unsignedInteger('points_threshold')->default(0);
            $t->json('benefits')->nullable();
            $t->decimal('rate_discount_pct', 6, 3)->default(0);
            $t->unsignedSmallInteger('display_order')->default(0);
            $t->timestamps();
            $t->unique(['property_id', 'slug']);
        });

        Schema::create('loyalty_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->unique()->constrained();
            $t->string('membership_no')->unique();
            $t->foreignId('tier_id')->nullable()->constrained('loyalty_tiers');
            $t->unsignedInteger('points_balance')->default(0);
            $t->unsignedInteger('lifetime_points')->default(0);
            $t->timestamp('enrolled_at')->useCurrent();
            $t->timestamp('tier_expires_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('loyalty_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('member_id')->constrained('loyalty_members')->cascadeOnDelete();
            $t->string('type'); // earn|redeem|adjust|expire
            $t->integer('points'); // positive earn, negative redeem
            $t->string('source_type')->nullable();
            $t->unsignedBigInteger('source_id')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_members');
        Schema::dropIfExists('loyalty_tiers');
    }
};
