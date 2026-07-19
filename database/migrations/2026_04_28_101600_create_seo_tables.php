<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('route_pattern');
            $t->string('slug');
            $t->string('url')->unique();
            $t->string('title');
            $t->text('description');
            $t->string('h1');
            $t->longText('intro_html')->nullable();
            $t->longText('outro_html')->nullable();
            $t->longText('content_html')->nullable();
            $t->json('faq_json')->nullable();
            $t->json('meta_json')->nullable();
            $t->string('locale', 5)->default('id');
            $t->string('status')->default('draft'); // draft|published
            $t->timestamp('last_generated_at')->nullable();
            $t->timestamp('regenerate_after')->nullable();
            $t->unsignedInteger('views_total')->default(0);
            $t->unsignedInteger('views_30d')->default(0);
            $t->unsignedInteger('conversions_30d')->default(0);
            $t->timestamps();
            $t->index(['property_id', 'status']);
        });

        Schema::create('seo_redirects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('source_path');
            $t->string('target_path');
            $t->unsignedSmallInteger('http_status')->default(301);
            $t->timestamps();
            $t->unique(['property_id', 'source_path']);
        });

        Schema::create('seo_keywords', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('keyword');
            $t->string('target_url');
            $t->unsignedInteger('current_rank')->nullable();
            $t->timestamp('last_checked_at')->nullable();
            $t->timestamps();
        });

        Schema::create('landmarks', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('name');
            $t->string('city');
            $t->string('province')->nullable();
            $t->string('country', 2)->default('ID');
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->text('description')->nullable();
            $t->json('photos')->nullable();
            $t->timestamps();
            $t->index(['city', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landmarks');
        Schema::dropIfExists('seo_keywords');
        Schema::dropIfExists('seo_redirects');
        Schema::dropIfExists('seo_pages');
    }
};
