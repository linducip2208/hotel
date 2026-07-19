<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // OTA Virtual Card — pay-on-arrival untuk Booking.com etc.
        Schema::create('ota_virtual_cards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('channel_id')->nullable()->constrained();
            $t->string('card_holder_masked');
            $t->string('card_number_encrypted');
            $t->string('card_brand')->nullable();
            $t->date('expires_on');
            $t->string('cvv_encrypted')->nullable();
            $t->decimal('amount_authorized', 14, 2);
            $t->decimal('amount_charged', 14, 2)->default(0);
            $t->date('valid_from');
            $t->date('valid_until');
            $t->string('status')->default('active'); // active|charged|expired|invalid
            $t->json('charge_attempts')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'status']);
        });

        Schema::create('cancellation_policies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code');
            $t->boolean('is_refundable')->default(true);
            $t->json('rules'); // array: [['days_before' => 3, 'penalty_pct' => 100], ...]
            $t->text('display_text')->nullable();
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'code']);
        });

        Schema::create('guest_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained();
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->foreignId('room_id')->nullable()->constrained();
            $t->string('category'); // amenity|housekeeping|maintenance|fnb|concierge|complaint|other
            $t->string('subject');
            $t->text('description')->nullable();
            $t->string('priority')->default('normal'); // low|normal|high|urgent
            $t->string('status')->default('open'); // open|in_progress|resolved|escalated|cancelled
            $t->foreignId('assignee_id')->nullable()->constrained('users');
            $t->timestamp('opened_at')->useCurrent();
            $t->timestamp('responded_at')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->unsignedSmallInteger('response_minutes')->nullable();
            $t->unsignedSmallInteger('resolution_minutes')->nullable();
            $t->text('resolution_notes')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_requests');
        Schema::dropIfExists('cancellation_policies');
        Schema::dropIfExists('ota_virtual_cards');
    }
};
