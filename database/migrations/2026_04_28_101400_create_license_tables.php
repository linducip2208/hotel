<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('local_licenses', function (Blueprint $t) {
            $t->id();
            $t->string('license_key_hash')->nullable();
            $t->text('token_encrypted')->nullable();
            $t->string('fingerprint')->nullable();
            $t->string('install_id')->nullable();
            $t->timestamp('paired_at')->nullable();
            $t->timestamp('last_heartbeat_attempt_at')->nullable();
            $t->timestamp('last_heartbeat_success_at')->nullable();
            $t->timestamp('grace_until')->nullable();
            $t->timestamp('valid_until')->nullable();
            $t->string('plan')->nullable();
            $t->json('features')->nullable();
            $t->unsignedInteger('max_rooms')->nullable();
            $t->unsignedInteger('max_users')->nullable();
            $t->unsignedInteger('max_properties')->nullable();
            $t->string('status')->default('unpaired'); // unpaired|paired|grace|degraded|locked|revoked
            $t->text('degrade_reason')->nullable();
            $t->timestamps();
        });

        Schema::create('license_events', function (Blueprint $t) {
            $t->id();
            $t->string('event'); // pairing.success|heartbeat.success|heartbeat.failed|degraded|revoked|migrated
            $t->json('payload')->nullable();
            $t->string('source_ip', 45)->nullable();
            $t->text('error')->nullable();
            $t->timestamps();
            $t->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_events');
        Schema::dropIfExists('local_licenses');
    }
};
