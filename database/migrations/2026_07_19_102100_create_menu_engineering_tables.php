<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained('pos_menu_items')->nullOnDelete();
            $table->string('name');
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->string('portion_size')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_recipe_id')->constrained('menu_recipes')->cascadeOnDelete();
            $table->string('ingredient_name');
            $table->decimal('quantity', 12, 4)->default(0);
            $table->string('unit')->default('gram');
            $table->decimal('cost_per_unit', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('menu_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_recipe_id')->constrained('menu_recipes')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('units_sold')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('gross_profit', 12, 2)->default(0);
            $table->decimal('profit_margin_pct', 6, 2)->default(0);
            $table->decimal('popularity_pct', 6, 2)->default(0);
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_performance');
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('menu_recipes');
    }
};
