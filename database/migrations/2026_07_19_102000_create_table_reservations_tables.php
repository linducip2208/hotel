<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained('pos_outlets')->nullOnDelete();
            $table->string('table_number');
            $table->string('section')->nullable();
            $table->integer('capacity')->default(4);
            $table->string('shape')->default('rectangle'); // rectangle|round|square|booth
            $table->boolean('is_active')->default(true);
            $table->boolean('is_accessible')->default(false);
            $table->decimal('min_spend', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('table_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_table_id')->constrained('restaurant_tables')->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_phone')->nullable();
            $table->integer('party_size');
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->default(90);
            $table->string('status')->default('confirmed'); // confirmed|seated|completed|no_show|cancelled
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('booked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_reservations');
        Schema::dropIfExists('restaurant_tables');
    }
};
