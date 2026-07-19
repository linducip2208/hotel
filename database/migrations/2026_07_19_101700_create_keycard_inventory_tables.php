<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('keycard_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('encoding_type')->default('rfid');
            $t->string('color')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('keycard_inventory', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('keycard_type_id')->constrained('keycard_types')->cascadeOnDelete();
            $t->string('card_number')->unique();
            $t->string('rfid_uid')->nullable()->unique();
            $t->string('status')->default('available');
            $t->foreignId('assigned_to_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $t->foreignId('assigned_to_reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $t->foreignId('current_guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $t->timestamp('issued_at')->nullable();
            $t->timestamp('returned_at')->nullable();
            $t->unsignedInteger('times_reused')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keycard_inventory');
        Schema::dropIfExists('keycard_types');
    }
};
