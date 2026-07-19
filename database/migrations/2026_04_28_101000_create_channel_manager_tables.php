<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('code'); // booking_com, agoda, traveloka, tiket, ...
            $t->string('name');
            $t->string('adapter_class');
            $t->text('credentials_encrypted')->nullable(); // encrypted ciphertext, not valid JSON
            $t->json('config')->nullable();
            $t->string('hotel_id_at_channel')->nullable();
            $t->boolean('is_active')->default(false);
            $t->boolean('two_way_sync')->default(true);
            $t->timestamp('last_sync_at')->nullable();
            $t->string('last_sync_status')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'code']);
        });

        Schema::create('channel_room_mappings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained();
            $t->foreignId('rate_plan_id')->constrained();
            $t->string('channel_room_id');
            $t->string('channel_rate_id');
            $t->json('config')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['channel_id', 'room_type_id', 'rate_plan_id'], 'crm_channel_rt_rp_unique');
        });

        Schema::create('ari_sync_log', function (Blueprint $t) {
            $t->id();
            $t->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $t->string('operation'); // push_availability|push_rates|push_restrictions|fetch_bookings
            $t->string('status'); // queued|running|success|failed
            $t->json('payload_summary')->nullable();
            $t->json('response_summary')->nullable();
            $t->text('error')->nullable();
            $t->unsignedInteger('attempt')->default(1);
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->timestamps();
            $t->index(['channel_id', 'created_at']);
        });

        Schema::create('channel_conflicts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('channel_id')->nullable()->constrained();
            $t->string('conflict_type'); // overbooking|rate_mismatch|inventory_mismatch
            $t->json('details');
            $t->string('status')->default('open'); // open|resolved|ignored
            $t->foreignId('resolved_by_user_id')->nullable()->constrained('users');
            $t->timestamp('resolved_at')->nullable();
            $t->text('resolution_notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_conflicts');
        Schema::dropIfExists('ari_sync_log');
        Schema::dropIfExists('channel_room_mappings');
        Schema::dropIfExists('channels');
    }
};
