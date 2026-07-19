<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('user_type')->nullable(); // staff|admin|api|system
            $t->string('action');
            $t->nullableMorphs('auditable');
            $t->json('before')->nullable();
            $t->json('after')->nullable();
            $t->string('ip', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->string('request_id')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['action', 'created_at']);
        });

        Schema::create('approval_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('requester_id')->constrained('users');
            $t->string('action_type');
            $t->json('payload');
            $t->string('status')->default('pending'); // pending|approved|rejected|expired
            $t->foreignId('approver_id')->nullable()->constrained('users');
            $t->timestamp('approved_at')->nullable();
            $t->text('approver_notes')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('webhooks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('url');
            $t->string('secret_encrypted');
            $t->json('events');
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('failed_consecutive')->default(0);
            $t->timestamp('last_delivered_at')->nullable();
            $t->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $t->string('event');
            $t->string('event_id')->index();
            $t->json('payload');
            $t->unsignedInteger('attempt')->default(1);
            $t->string('status')->default('pending'); // pending|success|failed|dead
            $t->unsignedSmallInteger('http_status')->nullable();
            $t->text('response_body')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamps();
        });

        Schema::create('api_idempotency_keys', function (Blueprint $t) {
            $t->id();
            $t->string('key', 80)->unique();
            $t->string('hash', 64);
            $t->json('response')->nullable();
            $t->unsignedSmallInteger('http_status')->nullable();
            $t->timestamp('expires_at');
            $t->timestamps();
        });

        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained();
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->unsignedTinyInteger('rating')->nullable();
            $t->json('category_ratings')->nullable();
            $t->text('comment')->nullable();
            $t->boolean('is_public')->default(false);
            $t->boolean('is_published')->default(false);
            $t->string('source')->default('internal'); // internal|google|booking|tripadvisor
            $t->timestamps();
        });

        Schema::create('promo_codes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('code')->unique();
            $t->string('description')->nullable();
            $t->string('discount_type'); // pct|amount|nights_free|upgrade
            $t->decimal('discount_value', 14, 2);
            $t->date('valid_from')->nullable();
            $t->date('valid_until')->nullable();
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('usage_count')->default(0);
            $t->json('rules')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('api_idempotency_keys');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('audit_logs');
    }
};
