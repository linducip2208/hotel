<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pb1_rates', function (Blueprint $t) {
            $t->id();
            $t->string('region_code')->index(); // ID-BA-BD, ID-JK, etc.
            $t->string('region_name');
            $t->decimal('rate', 6, 3); // 10.000 = 10%
            $t->date('effective_from');
            $t->date('effective_until')->nullable();
            $t->string('source_law')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['region_code', 'effective_from']);
        });

        Schema::create('wna_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained();
            $t->foreignId('guest_id')->constrained();
            $t->date('check_in_date');
            $t->date('check_out_date');
            $t->string('passport_no');
            $t->string('nationality', 2);
            $t->date('passport_expires_at')->nullable();
            $t->string('visa_type')->nullable();
            $t->string('arrival_card_no')->nullable();
            $t->string('reported_at_imigrasi_status')->default('pending');
            $t->timestamp('reported_at')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'check_in_date']);
        });

        Schema::create('nsfp_pools', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('range_start');
            $t->string('range_end');
            $t->string('current_serial');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nsfp_pools');
        Schema::dropIfExists('wna_logs');
        Schema::dropIfExists('pb1_rates');
    }
};
