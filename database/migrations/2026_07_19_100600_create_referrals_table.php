<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('referral_codes', function (Blueprint $t) {
            if (!Schema::hasColumn('referral_codes', 'total_referrals')) {
                $t->integer('total_referrals')->default(0)->after('uses_limit');
            }
            if (!Schema::hasColumn('referral_codes', 'total_rewards_earned')) {
                $t->decimal('total_rewards_earned', 14, 2)->default(0)->after('total_referrals');
            }
        });

        Schema::create('referrals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('referrer_guest_id')->constrained('guests');
            $t->foreignId('referred_guest_id')->constrained('guests');
            $t->foreignId('referral_code_id')->nullable()->constrained()->nullOnDelete();
            $t->string('status')->default('pending');
            $t->decimal('reward_amount', 14, 2)->default(0);
            $t->string('reward_type')->default('discount');
            $t->boolean('is_rewarded')->default(false);
            $t->timestamp('referred_at')->useCurrent();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');

        Schema::table('referral_codes', function (Blueprint $t) {
            $t->dropColumn(['total_rewards_earned', 'total_referrals']);
        });
    }
};
