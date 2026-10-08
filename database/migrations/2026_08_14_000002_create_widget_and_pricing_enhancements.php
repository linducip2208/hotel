<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══ 1. VIRTUAL TOUR (360°) ═══
        Schema::table('room_types', function (Blueprint $t) {
            $t->string('virtual_tour_url', 500)->nullable()->after('photos');
        });

        Schema::table('properties', function (Blueprint $t) {
            $t->string('virtual_tour_url', 500)->nullable()->after('logo_path');
        });

        // ═══ 2. GROUP-WISE PRICING (discount per guest group) ═══
        Schema::create('rate_group_discounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name'); // e.g. "Pemerintah", "Member", "Senior"
            $t->string('code', 50)->nullable(); // optional promo/group code
            $t->string('discount_type')->default('percent'); // percent|fixed
            $t->decimal('discount_value', 14, 2);
            $t->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $t->foreignId('rate_plan_id')->nullable()->constrained('rate_plans')->nullOnDelete();
            $t->integer('min_nights')->default(1);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_group_discounts');

        Schema::table('properties', function (Blueprint $t) {
            $t->dropColumn('virtual_tour_url');
        });

        Schema::table('room_types', function (Blueprint $t) {
            $t->dropColumn('virtual_tour_url');
        });
    }
};
