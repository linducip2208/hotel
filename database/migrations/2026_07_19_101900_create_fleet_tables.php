<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('plate_number');
            $table->string('type')->default('car'); // car|van|bus|motorcycle|golf_cart
            $table->integer('capacity')->default(4);
            $table->string('fuel_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('last_maintenance_at')->nullable();
            $table->date('next_maintenance_due')->nullable();
            $table->timestamps();
        });

        Schema::create('fleet_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fleet_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('fleet_vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('fleet_drivers')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('trip_type')->default('airport_pickup'); // airport_pickup|airport_dropoff|city_tour|custom|shuttle
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->datetime('scheduled_at');
            $table->datetime('actual_departure')->nullable();
            $table->datetime('actual_arrival')->nullable();
            $table->string('status')->default('scheduled'); // scheduled|in_progress|completed|cancelled
            $table->integer('passenger_count')->default(1);
            $table->decimal('charge_amount', 12, 2)->default(0);
            $table->foreignId('folio_charge_id')->nullable()->constrained('folio_charges')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shuttle_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('route_name');
            $table->string('from_location');
            $table->string('to_location');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->json('days_of_week')->nullable(); // [0,1,2,3,4,5,6]
            $table->integer('capacity')->default(12);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shuttle_schedules');
        Schema::dropIfExists('fleet_trips');
        Schema::dropIfExists('fleet_drivers');
        Schema::dropIfExists('fleet_vehicles');
    }
};
