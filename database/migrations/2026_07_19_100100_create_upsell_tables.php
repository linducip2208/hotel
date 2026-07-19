<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('upsell_offers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type'); // room_upgrade|late_checkout|spa|dinner|airport_transfer|package
            $t->text('description')->nullable();
            $t->decimal('price', 14, 2);
            $t->integer('min_stay_nights')->default(1);
            $t->string('target_guest_tier')->nullable(); // hot|warm|cold|all
            $t->string('timing')->default('pre_arrival'); // pre_arrival|during_stay|checkin|anytime
            $t->integer('days_before_arrival')->nullable();
            $t->foreignId('upgrade_to_room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('upsell_presentations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('upsell_offer_id')->constrained()->cascadeOnDelete();
            $t->string('status')->default('offered'); // offered|accepted|declined|expired
            $t->timestamp('offered_at')->useCurrent();
            $t->timestamp('responded_at')->nullable();
            $t->decimal('price_offered', 14, 2);
            $t->decimal('price_accepted', 14, 2)->nullable();
            $t->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('room_upgrades', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('from_room_id')->constrained('rooms');
            $t->foreignId('to_room_id')->constrained('rooms');
            $t->decimal('upgrade_fee', 14, 2)->default(0);
            $t->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_upgrades');
        Schema::dropIfExists('upsell_presentations');
        Schema::dropIfExists('upsell_offers');
    }
};
