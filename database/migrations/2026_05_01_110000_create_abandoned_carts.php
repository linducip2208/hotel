<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('abandoned_carts', function (Blueprint $t) {
            $t->id();
            $t->string('session_id')->unique();
            $t->string('guest_email')->nullable();
            $t->string('guest_name')->nullable();
            $t->json('cart_data');
            $t->string('recovery_token', 64)->unique();
            $t->timestamp('recovered_at')->nullable();
            $t->timestamp('expires_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
