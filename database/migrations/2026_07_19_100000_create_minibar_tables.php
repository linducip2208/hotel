<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('minibar_products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('category')->default('beverage');
            $t->decimal('selling_price', 14, 2);
            $t->decimal('cost_price', 14, 2)->default(0);
            $t->string('sku', 32)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('minibar_stocks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->foreignId('minibar_product_id')->constrained()->cascadeOnDelete();
            $t->integer('initial_qty')->default(3);
            $t->integer('current_qty')->default(3);
            $t->timestamps();
            $t->unique(['room_id', 'minibar_product_id']);
        });

        Schema::create('minibar_consumptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained();
            $t->foreignId('minibar_product_id')->constrained();
            $t->foreignId('charged_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->integer('qty')->default(1);
            $t->decimal('unit_price', 14, 2);
            $t->decimal('total_amount', 14, 2);
            $t->foreignId('folio_charge_id')->nullable()->constrained()->nullOnDelete();
            $t->date('consumption_date');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minibar_consumptions');
        Schema::dropIfExists('minibar_stocks');
        Schema::dropIfExists('minibar_products');
    }
};
