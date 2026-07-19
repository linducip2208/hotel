<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kids_activities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unsignedTinyInteger('age_min')->default(3);
            $t->unsignedTinyInteger('age_max')->default(12);
            $t->unsignedSmallInteger('capacity')->default(10);
            $t->decimal('price', 12, 2)->default(0);
            $t->unsignedSmallInteger('duration_minutes')->default(60);
            $t->json('schedule')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('kids_bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('kids_activity_id')->constrained('kids_activities')->cascadeOnDelete();
            $t->string('child_name');
            $t->unsignedTinyInteger('child_age');
            $t->date('booking_date');
            $t->time('start_time');
            $t->string('status')->default('booked');
            $t->text('special_requests')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kids_bookings');
        Schema::dropIfExists('kids_activities');
    }
};
