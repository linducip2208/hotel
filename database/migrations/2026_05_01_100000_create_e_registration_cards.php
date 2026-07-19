<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('e_registration_cards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $t->timestamp('signed_at')->nullable();
            $t->string('signature_image_path')->nullable();
            $t->foreignId('verified_by_staff_id')->nullable()->constrained('users');
            $t->boolean('is_verified')->default(false);
            $t->json('submitted_data')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_registration_cards');
    }
};
