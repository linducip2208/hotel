<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('integration_type'); // ai|payment|sms|whatsapp|mail|door_lock|rate_shopper|ota|storage|captcha|accounting_export|other
            $t->string('name'); // user input — e.g. "OpenAI Production", "Midtrans Sandbox"
            $t->string('slug');
            $t->string('api_format'); // openai_compatible|anthropic|gemini|redirect_flow|smtp|...
            $t->string('base_url')->nullable();
            $t->text('api_key_encrypted')->nullable();
            $t->text('secret_encrypted')->nullable();
            $t->json('extra_headers')->nullable();
            $t->json('extra_config')->nullable();
            $t->string('default_model')->nullable();
            $t->json('capabilities')->nullable();
            $t->json('pricing')->nullable(); // user-input rate per model/usage
            $t->boolean('is_active')->default(false);
            $t->boolean('is_default')->default(false);
            $t->unsignedSmallInteger('display_order')->default(0);
            $t->string('test_status')->nullable(); // ok|failed|untested
            $t->timestamp('last_tested_at')->nullable();
            $t->text('test_message')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'integration_type', 'is_active']);
            $t->unique(['property_id', 'integration_type', 'slug']);
        });

        Schema::create('provider_feature_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('feature'); // booking_payment|sms_otp|wa_confirm|mail_transactional|ai_translate|ai_demand_forecast|ai_review_reply
            $t->foreignId('provider_id')->constrained();
            $t->string('default_model')->nullable();
            $t->json('config')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_feature_assignments');
        Schema::dropIfExists('providers');
    }
};
