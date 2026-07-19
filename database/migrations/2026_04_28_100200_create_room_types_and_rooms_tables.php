<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('code'); // e.g. SUP, DLX
            $t->string('name');
            $t->string('slug');
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('max_occupancy')->default(2);
            $t->unsignedSmallInteger('max_adults')->default(2);
            $t->unsignedSmallInteger('max_children')->default(0);
            $t->unsignedSmallInteger('extra_bed_capacity')->default(0);
            $t->decimal('base_rate', 12, 2)->default(0);
            $t->json('amenities')->nullable();
            $t->json('photos')->nullable();
            $t->unsignedSmallInteger('size_sqm')->nullable();
            $t->string('view')->nullable();
            $t->string('bed_config')->nullable();
            $t->boolean('smoking')->default(false);
            $t->unsignedSmallInteger('display_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'code']);
            $t->unique(['property_id', 'slug']);
        });

        Schema::create('rooms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $t->string('number');
            $t->unsignedTinyInteger('floor')->nullable();
            $t->string('view')->nullable();
            $t->string('hk_status')->default('clean'); // clean|dirty|inspected|out_of_order
            $t->string('fo_status')->default('vacant'); // vacant|occupied|reserved
            $t->boolean('is_smoking')->default(false);
            $t->boolean('is_accessible')->default(false);
            $t->json('features')->nullable();
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'number']);
            $t->index(['property_id', 'hk_status']);
            $t->index(['property_id', 'fo_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
    }
};
