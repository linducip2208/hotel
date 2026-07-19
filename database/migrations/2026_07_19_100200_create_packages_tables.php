<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->decimal('base_price', 14, 2);
            $t->integer('min_nights')->default(1);
            $t->integer('max_nights')->nullable();
            $t->string('image_url')->nullable();
            $t->boolean('is_active')->default(true);
            $t->integer('display_order')->default(0);
            $t->timestamps();
        });

        Schema::create('package_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('package_id')->constrained()->cascadeOnDelete();
            $t->string('item_type');
            $t->foreignId('reference_id')->nullable();
            $t->string('name');
            $t->integer('quantity')->default(1);
            $t->decimal('unit_price', 14, 2)->default(0);
            $t->boolean('is_included')->default(true);
            $t->timestamps();
        });

        Schema::create('reservation_packages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('package_id')->constrained();
            $t->decimal('price_charged', 14, 2);
            $t->foreignId('folio_charge_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_packages');
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('packages');
    }
};
