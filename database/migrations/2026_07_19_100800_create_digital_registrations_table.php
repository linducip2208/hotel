<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('digital_registrations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->constrained();
            $t->string('status')->default('pending');
            $t->string('token', 64)->unique();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('viewed_at')->nullable();
            $t->timestamp('signed_at')->nullable();
            $t->json('form_data')->nullable();
            $t->string('signature_path')->nullable();
            $t->string('id_document_path')->nullable();
            $t->string('ip_address')->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_registrations');
    }
};
