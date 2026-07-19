<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('door_lock_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained();
            $t->foreignId('reservation_id')->nullable()->constrained();
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->string('event_type'); // unlock|lock|key_issued|key_revoked|alarm|battery_low
            $t->string('source')->nullable(); // mobile_key|nfc|pin|staff_card
            $t->json('payload')->nullable();
            $t->timestamp('occurred_at');
            $t->timestamps();
            $t->index(['property_id', 'occurred_at']);
        });

        Schema::create('rate_shopper_snapshots', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $t->date('check_date');
            $t->date('shopped_for_date');
            $t->json('competitor_set'); // array per competitor: name, ota_source, rate, available
            $t->decimal('our_rate', 14, 2)->nullable();
            $t->decimal('avg_competitor_rate', 14, 2)->nullable();
            $t->decimal('rate_index', 6, 3)->nullable(); // ARI vs comp set
            $t->timestamps();
            $t->index(['property_id', 'shopped_for_date']);
        });

        Schema::create('gds_bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('gds'); // sabre|amadeus|travelport
            $t->string('booking_locator');
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->json('payload');
            $t->timestamp('received_at')->useCurrent();
            $t->timestamps();
            $t->unique(['gds', 'booking_locator']);
        });

        // Inventory per stock item (linen, amenity, cleaning supply) — separate from F&B
        Schema::create('stock_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('sku', 32);
            $t->string('name');
            $t->string('category'); // linen|amenity|cleaning|fnb_raw|other
            $t->string('uom')->default('pcs');
            $t->decimal('current_qty', 14, 3)->default(0);
            $t->decimal('reorder_point', 14, 3)->default(0);
            $t->decimal('average_cost', 14, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'sku']);
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stock_item_id')->constrained('stock_items')->cascadeOnDelete();
            $t->string('movement_type'); // in|out|adjust|transfer
            $t->decimal('qty', 14, 3);
            $t->decimal('unit_cost', 14, 2)->default(0);
            $t->string('reference_type')->nullable();
            $t->unsignedBigInteger('reference_id')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('moved_at')->useCurrent();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_items');
        Schema::dropIfExists('gds_bookings');
        Schema::dropIfExists('rate_shopper_snapshots');
        Schema::dropIfExists('door_lock_events');
    }
};
