<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Points of Interest — local guide CMS
        Schema::create('points_of_interest', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('landmark_id')->nullable()->constrained();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('category'); // restaurant|attraction|shopping|transport|nightlife|culture|nature|spa
            $t->text('description')->nullable();
            $t->string('city')->nullable();
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->unsignedSmallInteger('distance_meters')->nullable();
            $t->unsignedTinyInteger('rating')->nullable();
            $t->json('photos')->nullable();
            $t->string('phone')->nullable();
            $t->string('website')->nullable();
            $t->json('opening_hours')->nullable();
            $t->boolean('is_recommended')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // Carbon footprint per stay
        Schema::create('carbon_footprints', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete();
            $t->decimal('energy_kwh', 14, 2)->default(0);
            $t->decimal('water_liters', 14, 2)->default(0);
            $t->decimal('waste_kg', 14, 2)->default(0);
            $t->decimal('co2e_kg', 14, 2)->default(0); // Total kg CO2 equivalent
            $t->json('breakdown')->nullable();
            $t->date('period_date');
            $t->timestamps();
            $t->index(['property_id', 'period_date']);
        });

        // Sustainability metrics (property-level)
        Schema::create('sustainability_metrics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->date('measurement_date');
            $t->string('metric'); // energy_kwh|water_m3|waste_kg|recycled_pct|renewable_pct
            $t->decimal('value', 16, 4);
            $t->string('unit')->nullable();
            $t->string('source')->nullable(); // meter|invoice|estimate
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'measurement_date', 'metric']);
        });

        // Custom translations per property (override default lang)
        Schema::create('property_translations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('locale', 5);
            $t->string('key');
            $t->text('value');
            $t->timestamps();
            $t->unique(['property_id', 'locale', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_translations');
        Schema::dropIfExists('sustainability_metrics');
        Schema::dropIfExists('carbon_footprints');
        Schema::dropIfExists('points_of_interest');
    }
};
