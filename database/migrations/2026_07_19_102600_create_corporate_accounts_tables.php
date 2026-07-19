<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('corporate_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('company_name');
            $t->string('tax_id')->nullable();
            $t->string('industry')->nullable();
            $t->string('contact_person')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->text('address')->nullable();
            $t->string('rate_agreement_type')->default('fixed'); // fixed|percentage_discount|dynamic
            $t->decimal('discount_pct', 5, 2)->default(0);
            $t->decimal('credit_limit', 14, 2)->default(0);
            $t->unsignedSmallInteger('payment_terms_days')->default(30);
            $t->date('contract_start')->nullable();
            $t->date('contract_end')->nullable();
            $t->unsignedInteger('annual_room_night_commitment')->default(0);
            $t->unsignedInteger('actual_room_nights')->default(0);
            $t->string('status')->default('active'); // active|suspended|expired
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('corporate_rates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('corporate_account_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $t->decimal('negotiated_rate', 12, 2);
            $t->json('blackout_dates')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('corporate_bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('corporate_account_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->string('booking_source')->default('direct'); // direct|travel_agent|online
            $t->decimal('rate_applied', 12, 2)->nullable();
            $t->decimal('discount_amount', 12, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_bookings');
        Schema::dropIfExists('corporate_rates');
        Schema::dropIfExists('corporate_accounts');
    }
};
