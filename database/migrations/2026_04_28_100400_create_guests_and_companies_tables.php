<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $t->string('first_name');
            $t->string('last_name')->nullable();
            $t->string('email')->nullable()->index();
            $t->string('phone')->nullable()->index();
            $t->string('country', 2)->default('ID');
            $t->string('nationality', 2)->nullable();
            $t->date('date_of_birth')->nullable();
            $t->string('gender', 10)->nullable();
            $t->string('id_type')->nullable(); // ktp|passport|sim|kitas
            $t->string('id_number')->nullable();
            $t->string('id_photo_path')->nullable();
            $t->date('id_expires_at')->nullable();
            $t->string('address_line1')->nullable();
            $t->string('city')->nullable();
            $t->string('province')->nullable();
            $t->string('postal_code', 12)->nullable();
            $t->boolean('is_vip')->default(false);
            $t->boolean('is_blacklisted')->default(false);
            $t->text('blacklist_reason')->nullable();
            $t->json('preferences')->nullable();
            $t->json('tags')->nullable();
            $t->text('notes')->nullable();
            $t->boolean('marketing_consent')->default(false);
            $t->timestamp('forgotten_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('legal_name')->nullable();
            $t->string('npwp')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('address_line1')->nullable();
            $t->string('city')->nullable();
            $t->string('country', 2)->default('ID');
            $t->decimal('credit_limit', 14, 2)->default(0);
            $t->unsignedSmallInteger('payment_terms_days')->default(30);
            $t->string('contract_no')->nullable();
            $t->date('contract_start')->nullable();
            $t->date('contract_end')->nullable();
            $t->json('negotiated_rates')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('travel_agents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('iata_code')->nullable();
            $t->decimal('default_commission_pct', 6, 3)->default(0);
            $t->decimal('credit_limit', 14, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_agents');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('guests');
    }
};
