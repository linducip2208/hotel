<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persists every outbound notification (email, WhatsApp, SMS) for audit,
 * deduplication (idempotency_key), and delivery status tracking.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('channel');                 // mail|whatsapp|sms|push
            $t->string('event');                   // booking_confirmed|checkin_reminder|post_stay|invoice|etc.
            $t->string('recipient');               // email or phone
            $t->string('notifiable_type')->nullable();
            $t->unsignedBigInteger('notifiable_id')->nullable();
            $t->string('status')->default('pending'); // pending|sent|delivered|failed|bounced
            $t->string('provider_used')->nullable();
            $t->string('provider_message_id')->nullable();
            $t->text('error')->nullable();
            $t->string('idempotency_key')->unique(); // prevent duplicates
            $t->json('metadata')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamps();
            $t->index(['notifiable_type', 'notifiable_id']);
            $t->index(['property_id', 'event', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
