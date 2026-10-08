<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══ 1. NEWSLETTER SUBSCRIBERS ═══
        Schema::create('newsletter_subscribers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('email', 191);
            $t->string('name')->nullable();
            $t->string('source')->default('website'); // website|booking|manual|import
            $t->string('status')->default('subscribed'); // subscribed|unsubscribed|bounced
            $t->string('unsubscribe_token', 64)->nullable()->index();
            $t->timestamp('subscribed_at')->nullable();
            $t->timestamp('unsubscribed_at')->nullable();
            $t->timestamps();

            $t->unique(['property_id', 'email']);
        });

        // ═══ 2. CMS / STATIC PAGES ═══
        Schema::create('cms_pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('slug', 191)->unique();
            $t->string('meta_title')->nullable();
            $t->string('meta_description')->nullable();
            $t->text('excerpt')->nullable();
            $t->longText('content'); // HTML
            $t->boolean('is_published')->default(false);
            $t->boolean('show_in_footer')->default(false);
            $t->integer('sort_order')->default(0);
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });

        // ═══ 3. TESTIMONIALS ═══
        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('guest_name');
            $t->string('guest_title')->nullable(); // e.g. "Business Traveler"
            $t->string('origin_city')->nullable();
            $t->tinyInteger('rating')->default(5); // 1-5
            $t->text('quote');
            $t->string('avatar_url')->nullable();
            $t->string('source')->default('curated'); // curated|review_import
            $t->boolean('is_active')->default(true);
            $t->integer('sort_order')->default(0);
            $t->timestamps();
        });

        // ═══ 4. SEARCH KEYWORD TRACKING ═══
        Schema::create('search_keywords', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('keyword', 191);
            $t->string('source', 40)->default('booking'); // booking|site|panel
            $t->integer('results_count')->default(0);
            $t->integer('hits')->default(0);
            $t->string('locale', 5)->nullable();
            $t->timestamp('last_searched_at')->nullable();
            $t->timestamps();

            $t->index(['property_id', 'source', 'keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_keywords');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
