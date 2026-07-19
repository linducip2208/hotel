<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('brand')->nullable();
            $t->string('legal_name')->nullable();
            $t->string('npwp')->nullable();
            $t->boolean('is_pkp')->default(false);
            $t->string('nsfp_series')->nullable();
            $t->string('region_code')->index();
            $t->string('country', 2)->default('ID');
            $t->string('province')->nullable();
            $t->string('city')->nullable();
            $t->string('district')->nullable();
            $t->string('postal_code', 12)->nullable();
            $t->string('address_line1')->nullable();
            $t->string('address_line2')->nullable();
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->string('timezone')->default('Asia/Jakarta');
            $t->string('currency_default', 3)->default('IDR');
            $t->string('locale_default', 5)->default('id');
            $t->unsignedTinyInteger('star_rating')->nullable();
            $t->unsignedSmallInteger('total_rooms')->default(0);
            $t->time('check_in_time')->default('14:00:00');
            $t->time('check_out_time')->default('12:00:00');
            $t->string('owner_name')->nullable();
            $t->string('owner_email')->nullable();
            $t->string('owner_phone')->nullable();
            $t->string('logo_path')->nullable();
            $t->json('theme')->nullable();
            $t->json('settings')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
