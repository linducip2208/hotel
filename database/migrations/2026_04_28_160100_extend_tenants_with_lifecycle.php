<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $t) {
            $t->json('feature_overrides_locked')->nullable()->after('feature_overrides');
            $t->timestamp('suspended_at')->nullable()->after('current_period_ends_at');
            $t->timestamp('churned_at')->nullable()->after('suspended_at');
            $t->string('churn_reason')->nullable();
            $t->json('lifecycle_events')->nullable();
            $t->boolean('provisioned')->default(false);
            $t->timestamp('provisioned_at')->nullable();
            $t->index('status');
        });

        Schema::create('tenant_subscriptions', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id');
            $t->foreignId('plan_id')->constrained();
            $t->string('status'); // trialing|active|past_due|cancelled
            $t->date('current_period_start');
            $t->date('current_period_end');
            $t->date('trial_ends_at')->nullable();
            $t->string('billing_cycle')->default('monthly');
            $t->decimal('price_paid_idr', 14, 2)->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->index(['tenant_id', 'status']);
        });

        Schema::create('tenant_invoices', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id');
            $t->foreignId('subscription_id')->nullable()->constrained('tenant_subscriptions')->nullOnDelete();
            $t->string('invoice_no')->unique();
            $t->date('issued_at');
            $t->date('due_at');
            $t->decimal('subtotal', 14, 2);
            $t->decimal('tax_total', 14, 2)->default(0);
            $t->decimal('grand_total', 14, 2);
            $t->decimal('paid_total', 14, 2)->default(0);
            $t->decimal('balance', 14, 2);
            $t->string('status')->default('open');
            $t->json('line_items')->nullable();
            $t->string('payment_url')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invoices');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::table('tenants', function (Blueprint $t) {
            $t->dropIndex(['status']);
            $t->dropColumn(['feature_overrides_locked', 'suspended_at', 'churned_at', 'churn_reason', 'lifecycle_events', 'provisioned', 'provisioned_at']);
        });
    }
};
