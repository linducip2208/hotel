<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('ref')->unique(); // public reference (e.g. HMS-2026-000123)
            $t->foreignId('primary_guest_id')->constrained('guests');
            $t->foreignId('company_id')->nullable()->constrained();
            $t->foreignId('travel_agent_id')->nullable()->constrained();
            $t->string('source')->default('direct'); // direct|walk_in|api|ota:booking|ota:agoda|ota:traveloka|...
            $t->string('source_ref')->nullable(); // OTA booking id
            $t->date('check_in');
            $t->date('check_out');
            $t->unsignedSmallInteger('nights');
            $t->unsignedSmallInteger('adults')->default(1);
            $t->unsignedSmallInteger('children')->default(0);
            $t->json('children_ages')->nullable();
            $t->string('status')->default('confirmed'); // tentative|confirmed|checked_in|checked_out|cancelled|no_show
            $t->decimal('total_room', 14, 2)->default(0);
            $t->decimal('total_addons', 14, 2)->default(0);
            $t->decimal('service_charge', 14, 2)->default(0);
            $t->decimal('tax_total', 14, 2)->default(0);
            $t->decimal('grand_total', 14, 2)->default(0);
            $t->decimal('balance', 14, 2)->default(0);
            $t->string('currency', 3)->default('IDR');
            $t->string('promo_code')->nullable();
            $t->decimal('discount_amount', 14, 2)->default(0);
            $t->text('special_requests')->nullable();
            $t->text('notes_internal')->nullable();
            $t->timestamp('arrival_time')->nullable();
            $t->boolean('pre_checkin_complete')->default(false);
            $t->timestamp('checked_in_at')->nullable();
            $t->timestamp('checked_out_at')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->text('cancellation_reason')->nullable();
            $t->decimal('cancellation_penalty', 14, 2)->default(0);
            $t->foreignId('created_by_user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->softDeletes();
            $t->index(['property_id', 'check_in']);
            $t->index(['property_id', 'status']);
            $t->index(['property_id', 'source']);
        });

        Schema::create('reservation_rooms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained();
            $t->foreignId('rate_plan_id')->constrained();
            $t->foreignId('room_id')->nullable()->constrained();
            $t->date('check_in');
            $t->date('check_out');
            $t->unsignedSmallInteger('adults')->default(1);
            $t->unsignedSmallInteger('children')->default(0);
            $t->decimal('subtotal', 14, 2);
            $t->json('per_night_rates')->nullable();
            $t->string('status')->default('booked');
            $t->timestamps();
            $t->index('check_in');
        });

        Schema::create('reservation_guests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reservation_room_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->constrained();
            $t->boolean('is_primary')->default(false);
            $t->timestamps();
        });

        Schema::create('reservation_addons', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->string('code');
            $t->string('name');
            $t->unsignedInteger('qty')->default(1);
            $t->decimal('unit_price', 12, 2);
            $t->decimal('subtotal', 14, 2);
            $t->date('date_apply')->nullable();
            $t->timestamps();
        });

        Schema::create('booking_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->string('token_hashed', 128)->unique();
            $t->string('purpose'); // manage|pre_checkin|in_stay|review
            $t->timestamp('expires_at');
            $t->timestamp('used_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_access_tokens');
        Schema::dropIfExists('reservation_addons');
        Schema::dropIfExists('reservation_guests');
        Schema::dropIfExists('reservation_rooms');
        Schema::dropIfExists('reservations');
    }
};
