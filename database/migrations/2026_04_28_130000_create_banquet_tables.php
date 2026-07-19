<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('function_rooms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code');
            $t->unsignedSmallInteger('capacity_classroom')->nullable();
            $t->unsignedSmallInteger('capacity_theatre')->nullable();
            $t->unsignedSmallInteger('capacity_banquet')->nullable();
            $t->unsignedSmallInteger('capacity_ushape')->nullable();
            $t->unsignedSmallInteger('size_sqm')->nullable();
            $t->json('amenities')->nullable();
            $t->json('photos')->nullable();
            $t->decimal('half_day_rate', 12, 2)->nullable();
            $t->decimal('full_day_rate', 12, 2)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'code']);
        });

        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('event_no')->unique();
            $t->string('title');
            $t->string('event_type'); // wedding|meeting|gala|conference|seminar|other
            $t->foreignId('function_room_id')->constrained();
            $t->foreignId('company_id')->nullable()->constrained();
            $t->foreignId('primary_contact_guest_id')->nullable()->constrained('guests');
            $t->date('event_date');
            $t->time('start_time');
            $t->time('end_time');
            $t->string('setup')->nullable(); // classroom|theatre|banquet|ushape
            $t->unsignedSmallInteger('expected_attendees');
            $t->decimal('venue_rate', 14, 2)->default(0);
            $t->decimal('fnb_total', 14, 2)->default(0);
            $t->decimal('addons_total', 14, 2)->default(0);
            $t->decimal('grand_total', 14, 2)->default(0);
            $t->decimal('deposit_paid', 14, 2)->default(0);
            $t->decimal('balance', 14, 2)->default(0);
            $t->string('status')->default('inquiry'); // inquiry|tentative|definite|completed|cancelled
            $t->text('notes')->nullable();
            $t->json('av_equipment')->nullable();
            $t->timestamps();
        });

        Schema::create('event_menu_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unsignedInteger('qty');
            $t->decimal('unit_price', 12, 2);
            $t->decimal('subtotal', 14, 2);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_menu_items');
        Schema::dropIfExists('events');
        Schema::dropIfExists('function_rooms');
    }
};
