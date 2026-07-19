<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('icon')->default('cake');
            $t->integer('min_guests')->default(10);
            $t->integer('max_guests')->default(500);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('event_bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('event_name');
            $t->foreignId('event_type_id')->constrained('event_types');
            $t->foreignId('guest_id')->constrained('guests');
            $t->date('event_date');
            $t->time('start_time');
            $t->time('end_time');
            $t->integer('expected_guests');
            $t->foreignId('venue_id')->nullable()->constrained('rooms')->nullOnDelete();
            $t->string('status')->default('inquiry');
            $t->decimal('total_quoted', 14, 2)->default(0);
            $t->decimal('deposit_paid', 14, 2)->default(0);
            $t->foreignId('folio_id')->nullable()->constrained('folios')->nullOnDelete();
            $t->json('setup_requirements')->nullable();
            $t->json('catering_requirements')->nullable();
            $t->text('special_requests')->nullable();
            $t->text('internal_notes')->nullable();
            $t->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('event_services', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_booking_id')->constrained('event_bookings')->cascadeOnDelete();
            $t->string('service_name');
            $t->string('vendor_name')->nullable();
            $t->decimal('cost', 14, 2)->default(0);
            $t->decimal('sell_price', 14, 2)->default(0);
            $t->string('status')->default('pending');
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_services');
        Schema::dropIfExists('event_bookings');
        Schema::dropIfExists('event_types');
    }
};
