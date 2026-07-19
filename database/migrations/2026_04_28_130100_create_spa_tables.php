<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spa_treatments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code');
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('duration_minutes');
            $t->decimal('price', 12, 2);
            $t->json('inclusions')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'code']);
        });

        Schema::create('spa_therapists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('gender', 1)->nullable();
            $t->json('specialties')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('spa_cabins', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type')->default('single'); // single|couple|vip
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('spa_appointments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('treatment_id')->constrained('spa_treatments');
            $t->foreignId('therapist_id')->nullable()->constrained('spa_therapists');
            $t->foreignId('cabin_id')->nullable()->constrained('spa_cabins');
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->foreignId('reservation_id')->nullable()->constrained();
            $t->foreignId('folio_id')->nullable()->constrained();
            $t->dateTime('start_at');
            $t->dateTime('end_at');
            $t->string('status')->default('booked'); // booked|in_progress|completed|cancelled|no_show
            $t->decimal('price', 12, 2);
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spa_appointments');
        Schema::dropIfExists('spa_cabins');
        Schema::dropIfExists('spa_therapists');
        Schema::dropIfExists('spa_treatments');
    }
};
