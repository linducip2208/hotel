<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabel ini hanya digunakan saat APP_MODE=saas (central control plane).
     * Tetap migrate di standalone untuk konsistensi schema; tabel akan kosong.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->decimal('monthly_price_idr', 14, 2)->nullable();
            $t->decimal('yearly_price_idr', 14, 2)->nullable();
            $t->decimal('per_room_price_idr', 14, 2)->nullable();
            $t->unsignedInteger('max_rooms')->nullable();
            $t->unsignedInteger('max_users')->nullable();
            $t->unsignedInteger('max_properties')->nullable()->default(1);
            $t->json('features')->nullable();
            $t->boolean('is_active')->default(true);
            $t->boolean('is_default_signup')->default(false);
            $t->unsignedSmallInteger('display_order')->default(0);
            $t->timestamps();
        });

        Schema::create('tenants', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('slug')->unique();
            $t->string('company_name');
            $t->string('owner_name');
            $t->string('owner_email');
            $t->string('owner_phone')->nullable();
            $t->string('status')->default('trial'); // trial|active|suspended|churned
            $t->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('trial_ends_at')->nullable();
            $t->timestamp('current_period_ends_at')->nullable();
            $t->unsignedInteger('max_rooms')->nullable();
            $t->unsignedInteger('max_users')->nullable();
            $t->string('db_name')->nullable();
            $t->string('db_host')->nullable();
            $t->string('storage_disk_path')->nullable();
            $t->json('feature_overrides')->nullable();
            $t->timestamp('last_active_at')->nullable();
            $t->timestamps();
        });

        Schema::create('tenant_domains', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id');
            $t->string('domain')->unique();
            $t->boolean('is_primary')->default(false);
            $t->boolean('is_verified')->default(false);
            $t->string('ssl_status')->default('none'); // none|provisioning|active|failed
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_domains');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('plans');
    }
};
