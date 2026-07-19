<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parking_slots', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('slot_number');
            $t->string('area')->default('main');
            $t->string('status')->default('available');
            $t->boolean('is_vip')->default(false);
            $t->timestamps();
        });

        Schema::create('parking_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parking_slot_id')->constrained('parking_slots');
            $t->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $t->string('vehicle_plate');
            $t->string('vehicle_type')->default('car');
            $t->string('vehicle_brand')->nullable();
            $t->string('vehicle_color')->nullable();
            $t->timestamp('check_in')->useCurrent();
            $t->timestamp('check_out')->nullable();
            $t->decimal('daily_rate', 14, 2)->default(0);
            $t->decimal('total_charge', 14, 2)->default(0);
            $t->boolean('is_valet')->default(false);
            $t->foreignId('valet_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->json('valet_key_location')->nullable();
            $t->string('status')->default('parked');
            $t->foreignId('folio_charge_id')->nullable()->constrained('folio_charges')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_records');
        Schema::dropIfExists('parking_slots');
    }
};
