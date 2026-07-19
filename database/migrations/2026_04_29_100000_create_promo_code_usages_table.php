<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promo_code_usages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->decimal('discount_applied', 14, 2)->default(0);
            $t->string('currency', 3)->default('IDR');
            $t->timestamp('used_at')->useCurrent();
            $t->timestamps();
            $t->index(['promo_code_id', 'used_at']);
        });

        // Add FK from rate_plans to cancellation_policies (optional policy link)
        Schema::table('rate_plans', function (Blueprint $t) {
            $t->foreignId('cancellation_policy_id')
                ->nullable()
                ->after('parent_rate_plan_id')
                ->constrained('cancellation_policies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rate_plans', function (Blueprint $t) {
            $t->dropForeign(['cancellation_policy_id']);
            $t->dropColumn('cancellation_policy_id');
        });
        Schema::dropIfExists('promo_code_usages');
    }
};
