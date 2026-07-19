<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('password');
            $t->string('name');
            $t->string('phone')->nullable();
            $t->string('role')->default('support'); // super_admin|sales|support|finance|dev_ops|read_only
            $t->json('permissions')->nullable();
            $t->string('two_factor_secret_encrypted')->nullable();
            $t->text('two_factor_recovery_codes')->nullable(); // encrypted ciphertext, not valid JSON
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_login_at')->nullable();
            $t->string('last_login_ip', 45)->nullable();
            $t->rememberToken();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
