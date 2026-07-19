<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drip_campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('trigger_event');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('drip_steps', function (Blueprint $t) {
            $t->id();
            $t->foreignId('drip_campaign_id')->constrained()->cascadeOnDelete();
            $t->integer('delay_hours');
            $t->string('channel')->default('whatsapp');
            $t->string('template_key');
            $t->string('subject')->nullable();
            $t->text('message');
            $t->integer('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('drip_queue', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('drip_step_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->constrained();
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('scheduled_at');
            $t->string('status')->default('pending');
            $t->timestamp('sent_at')->nullable();
            $t->text('error')->nullable();
            $t->timestamps();
            $t->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_queue');
        Schema::dropIfExists('drip_steps');
        Schema::dropIfExists('drip_campaigns');
    }
};
