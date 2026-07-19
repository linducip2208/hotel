<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->foreignId('reservation_id')->nullable()->constrained();
            $t->string('channel'); // email|whatsapp|sms|web_chat|ota_message
            $t->string('external_thread_id')->nullable();
            $t->string('subject')->nullable();
            $t->string('status')->default('open'); // open|pending|resolved|archived
            $t->foreignId('assignee_id')->nullable()->constrained('users');
            $t->string('sentiment')->nullable(); // positive|neutral|negative
            $t->timestamp('last_message_at')->nullable();
            $t->unsignedSmallInteger('unread_count')->default(0);
            $t->timestamps();
        });

        Schema::create('messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('thread_id')->constrained('message_threads')->cascadeOnDelete();
            $t->string('direction'); // inbound|outbound
            $t->string('from')->nullable();
            $t->string('to')->nullable();
            $t->text('body');
            $t->json('attachments')->nullable();
            $t->string('status')->default('delivered'); // queued|sent|delivered|read|failed
            $t->json('raw_payload')->nullable();
            $t->timestamps();
        });

        Schema::create('message_templates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('channel'); // email|whatsapp|sms
            $t->string('subject')->nullable();
            $t->text('body');
            $t->json('variables')->nullable();
            $t->string('locale', 5)->default('id');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('marketing_campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('channel'); // email|whatsapp|sms
            $t->foreignId('template_id')->nullable()->constrained('message_templates');
            $t->json('audience_filter')->nullable();
            $t->timestamp('scheduled_at')->nullable();
            $t->string('status')->default('draft'); // draft|scheduled|sending|sent|cancelled
            $t->unsignedInteger('recipients_count')->default(0);
            $t->unsignedInteger('sent_count')->default(0);
            $t->unsignedInteger('opened_count')->default(0);
            $t->unsignedInteger('clicked_count')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
    }
};
