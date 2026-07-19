<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guest 360 Intelligence: aggregated behavioral profile rebuilt after each stay.
 * Separates mutable computed data from the immutable guest identity record.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('guest_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('guest_id')->unique()->constrained()->cascadeOnDelete();

            // Stay patterns
            $t->unsignedSmallInteger('total_stays')->default(0);
            $t->unsignedSmallInteger('total_nights')->default(0);
            $t->decimal('total_lifetime_value', 16, 2)->default(0);
            $t->decimal('avg_daily_rate', 14, 2)->default(0);
            $t->decimal('avg_fnb_spend_per_stay', 14, 2)->default(0);
            $t->decimal('avg_spa_spend_per_stay', 14, 2)->default(0);
            $t->decimal('avg_ancillary_spend', 14, 2)->default(0);

            // Preferences (auto-inferred)
            $t->string('preferred_room_type_id')->nullable();   // most booked
            $t->string('preferred_floor')->nullable();
            $t->string('preferred_bed_type')->nullable();
            $t->string('preferred_check_in_day')->nullable();   // Mon/Fri/etc.
            $t->unsignedTinyInteger('avg_party_size')->default(1);
            $t->boolean('typically_books_breakfast')->default(false);
            $t->boolean('typically_uses_spa')->default(false);
            $t->boolean('typically_uses_fnb')->default(false);

            // Booking behavior
            $t->unsignedSmallInteger('avg_lead_days')->default(0); // days before checkin
            $t->string('primary_booking_source')->nullable();      // direct|booking_com|etc.
            $t->unsignedTinyInteger('avg_stay_length')->default(1);
            $t->string('visit_frequency')->nullable();  // weekly|monthly|quarterly|annual|one_time

            // Risk & value scores (0-100)
            $t->unsignedTinyInteger('upsell_score')->default(0);   // likelihood to upgrade
            $t->unsignedTinyInteger('churn_risk_score')->default(0); // likelihood to not return
            $t->unsignedTinyInteger('loyalty_score')->default(0);

            // Sentiment
            $t->decimal('avg_review_score', 4, 2)->nullable();
            $t->unsignedSmallInteger('total_reviews')->default(0);
            $t->string('sentiment')->nullable(); // positive|neutral|negative

            $t->timestamp('last_built_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_profiles');
    }
};
