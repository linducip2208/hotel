<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Survey builder for post-stay
        Schema::create('surveys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug');
            $t->string('trigger')->default('post_stay'); // post_stay|post_event|in_stay|on_demand
            $t->json('questions'); // array of {key, type, prompt, required, options?}
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'slug']);
        });

        Schema::create('survey_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained();
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->json('answers');
            $t->unsignedTinyInteger('nps_score')->nullable();
            $t->string('sentiment')->nullable();
            $t->timestamp('submitted_at')->useCurrent();
            $t->timestamps();
        });

        // Referral program
        Schema::create('referral_codes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('owner_guest_id')->constrained('guests');
            $t->string('code', 24)->unique();
            $t->decimal('referrer_reward_amount', 14, 2)->default(0);
            $t->decimal('referee_discount_pct', 6, 3)->default(0);
            $t->unsignedInteger('uses_count')->default(0);
            $t->unsignedInteger('uses_limit')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('referral_redemptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('referral_code_id')->constrained();
            $t->foreignId('reservation_id')->constrained();
            $t->decimal('discount_applied', 14, 2);
            $t->decimal('reward_credited', 14, 2);
            $t->timestamps();
        });

        // Document templates per property (folio, invoice, BEO, contract)
        Schema::create('document_templates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type'); // folio|invoice|beo|contract|registration_card|email_confirmation
            $t->string('locale', 5)->default('id');
            $t->longText('header_html')->nullable();
            $t->longText('body_html');
            $t->longText('footer_html')->nullable();
            $t->json('css')->nullable();
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'type', 'locale', 'name']);
        });

        // Knowledge base articles
        Schema::create('kb_articles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->nullable()->constrained()->nullOnDelete(); // null = global vendor docs
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('category')->nullable();
            $t->longText('body');
            $t->json('tags')->nullable();
            $t->string('locale', 5)->default('id');
            $t->boolean('is_published')->default(false);
            $t->boolean('is_public')->default(false); // public = guests can read
            $t->unsignedInteger('views_count')->default(0);
            $t->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_articles');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('referral_redemptions');
        Schema::dropIfExists('referral_codes');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('surveys');
    }
};
