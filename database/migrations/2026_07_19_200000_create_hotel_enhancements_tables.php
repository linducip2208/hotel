<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ═══ 1. MICRO-STAY / DAY-USE ═══
        Schema::create('microstay_rates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $t->integer('hours'); // 3, 6, 12
            $t->decimal('price', 14, 2);
            $t->time('earliest_checkin')->default('08:00');
            $t->time('latest_checkin')->default('22:00');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::table('reservations', function (Blueprint $t) {
            $t->boolean('is_microstay')->default(false)->after('grand_total');
            $t->integer('microstay_hours')->nullable()->after('is_microstay');
            $t->dateTime('check_out_hour')->nullable()->after('microstay_hours');
        });

        // ═══ 2. DYNAMIC PACKAGING ═══
        Schema::table('packages', function (Blueprint $t) {
            $t->boolean('is_dynamic')->default(false)->after('is_active');
            $t->json('dynamic_options')->nullable()->after('is_dynamic'); // [{type, reference_id, name, price_modifier}]
            $t->decimal('price_from', 14, 2)->nullable()->after('base_price');
            $t->decimal('price_to', 14, 2)->nullable()->after('price_from');
        });

        Schema::create('package_customizations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reservation_package_id')->constrained()->cascadeOnDelete();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('option_type');
            $t->foreignId('reference_id')->nullable();
            $t->string('name');
            $t->decimal('price_modifier', 14, 2)->default(0);
            $t->timestamps();
        });

        // ═══ 3. SELF CHECK-IN KIOSK ═══
        Schema::create('kiosk_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $t->string('session_code', 6)->unique();
            $t->string('status')->default('started'); // started|verified|signed|completed|cancelled
            $t->string('id_type')->nullable(); // ktp|passport|sim
            $t->string('id_number')->nullable();
            $t->text('id_ocr_data')->nullable();
            $t->text('signature_data')->nullable(); // base64
            $t->string('photo_path')->nullable();
            $t->string('room_assigned')->nullable();
            $t->text('terms_accepted')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        // ═══ 4. GUEST MESSAGING — enhance existing ═══
        Schema::table('messages', function (Blueprint $t) {
            $t->string('message_type')->default('text')->after('direction'); // text|image|file|template
            $t->boolean('is_read')->default(false)->after('status');
            $t->timestamp('read_at')->nullable()->after('is_read');
        });

        Schema::create('quick_replies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('label'); // button text
            $t->string('reply_text'); // auto-reply content
            $t->integer('display_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // ═══ 5. UPSELL PRE-ARRIVAL — enhance ═══
        Schema::table('upsell_offers', function (Blueprint $t) {
            $t->string('image_url')->nullable()->after('description');
            $t->integer('priority')->default(0)->after('timing');
        });

        Schema::create('upsell_campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->json('offer_ids'); // array of upsell_offer_id
            $t->string('status')->default('draft'); // draft|active|paused|completed
            $t->integer('days_before_arrival')->default(3);
            $t->string('channel')->default('whatsapp'); // whatsapp|email|both
            $t->json('guest_filters')->nullable(); // tier, nationality, vip, min_lifetime_value
            $t->integer('sent_count')->default(0);
            $t->integer('accepted_count')->default(0);
            $t->decimal('revenue_generated', 14, 2)->default(0);
            $t->timestamp('started_at')->nullable();
            $t->timestamp('ended_at')->nullable();
            $t->timestamps();
        });

        Schema::create('upsell_campaign_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('upsell_campaign_id')->constrained()->cascadeOnDelete();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $t->string('channel');
            $t->string('status')->default('sent'); // sent|delivered|clicked|accepted|declined|failed
            $t->text('raw_response')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('responded_at')->nullable();
            $t->timestamps();
        });

        // ═══ 6. RFM SEGMENTATION ═══
        Schema::table('guest_profiles', function (Blueprint $t) {
            $t->integer('recency_score')->nullable()->after('loyalty_score');  // 1-5
            $t->integer('frequency_score')->nullable()->after('recency_score'); // 1-5
            $t->integer('monetary_score')->nullable()->after('frequency_score'); // 1-5
            $t->integer('rfm_score')->nullable()->after('monetary_score');       // 3-15
            $t->string('rfm_segment')->nullable()->after('rfm_score');  // vip|at_risk|lost|new|potential|regular|hibernating
            $t->timestamp('rfm_calculated_at')->nullable()->after('rfm_segment');
        });

        Schema::create('rfm_segment_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('segment_name'); // vip, at_risk, lost, etc.
            $t->integer('recency_min')->default(1);
            $t->integer('frequency_min')->default(1);
            $t->integer('monetary_min')->default(1);
            $t->string('color')->default('slate');
            $t->text('description')->nullable();
            $t->json('auto_actions')->nullable(); // type: send_offer|notify_staff|tag_guest
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // ═══ 7. GUEST PREFERENCE ENGINE — enhance ═══
        Schema::table('guests', function (Blueprint $t) {
            $t->foreignId('auto_assigned_room_type_id')->nullable()->after('forgotten_at')->constrained('room_types')->nullOnDelete();
            $t->json('preference_confidence')->nullable()->after('preferences'); // {floor: 0.8, pillow: 0.6, ...}
        });

        Schema::create('guest_preference_history', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->string('source'); // booking|stay|survey|staff|auto_learned
            $t->string('preference_key');
            $t->string('preference_value');
            $t->decimal('confidence', 3, 2)->default(0.5);
            $t->timestamps();
        });

        // ═══ 8. RATE SCRAPER — enhance ═══
        Schema::create('rate_scraper_targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name')->comment('Competitor hotel name');
            $t->string('website_url')->nullable();
            $t->json('ota_urls')->nullable(); // {booking: url, agoda: url, ...}
            $t->json('room_type_mapping')->nullable(); // {our_room_type_id: their_room_name}
            $t->integer('stars')->nullable();
            $t->string('address')->nullable();
            $t->decimal('distance_km', 5, 1)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('rate_scraper_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rate_scraper_target_id')->constrained()->cascadeOnDelete();
            $t->date('scraped_for_date');
            $t->string('source'); // direct|booking|agoda|traveloka|etc.
            $t->json('rates_found')->nullable(); // [{room_type: ..., rate: ..., currency: ...}]
            $t->decimal('our_price', 14, 2)->nullable();
            $t->decimal('min_competitor_price', 14, 2)->nullable();
            $t->decimal('price_gap_pct', 5, 2)->nullable();
            $t->string('status')->default('success'); // success|failed|stale
            $t->text('error_message')->nullable();
            $t->timestamps();
        });

        Schema::table('rate_shopper_snapshots', function (Blueprint $t) {
            $t->foreignId('rate_scraper_target_id')->nullable()->after('property_id')->constrained()->nullOnDelete();
        });

        // ═══ ENHANCED PACKAGE TABLE ═══
        Schema::table('rate_shopper_snapshots', function (Blueprint $t) {
            $t->boolean('alert_sent')->default(false)->after('rate_index');
            $t->decimal('alert_threshold_pct', 5, 2)->nullable()->after('alert_sent');
        });

        Schema::create('rate_scraper_alerts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rate_scraper_log_id')->constrained()->cascadeOnDelete();
            $t->decimal('price_gap_pct', 5, 2);
            $t->string('alert_type')->default('price_gap'); // price_gap|parity_violation|rate_change
            $t->string('severity')->default('warning'); // info|warning|critical
            $t->text('message');
            $t->boolean('is_read')->default(false);
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_scraper_alerts');
        Schema::table('rate_shopper_snapshots', function (Blueprint $t) {
            $t->dropColumn(['alert_sent', 'alert_threshold_pct', 'rate_scraper_target_id']);
        });
        Schema::dropIfExists('rate_scraper_logs');
        Schema::dropIfExists('rate_scraper_targets');

        Schema::dropIfExists('guest_preference_history');
        Schema::table('guests', function (Blueprint $t) {
            $t->dropConstrainedForeignId('auto_assigned_room_type_id');
            $t->dropColumn('preference_confidence');
        });

        Schema::dropIfExists('rfm_segment_rules');
        Schema::table('guest_profiles', function (Blueprint $t) {
            $t->dropColumn(['recency_score', 'frequency_score', 'monetary_score', 'rfm_score', 'rfm_segment', 'rfm_calculated_at']);
        });

        Schema::dropIfExists('upsell_campaign_logs');
        Schema::dropIfExists('upsell_campaigns');
        Schema::table('upsell_offers', function (Blueprint $t) {
            $t->dropColumn(['image_url', 'priority']);
        });

        Schema::dropIfExists('quick_replies');
        Schema::table('messages', function (Blueprint $t) {
            $t->dropColumn(['message_type', 'is_read', 'read_at']);
        });

        Schema::dropIfExists('kiosk_sessions');

        Schema::dropIfExists('package_customizations');
        Schema::table('packages', function (Blueprint $t) {
            $t->dropColumn(['is_dynamic', 'dynamic_options', 'price_from', 'price_to']);
        });

        Schema::table('reservations', function (Blueprint $t) {
            $t->dropColumn(['is_microstay', 'microstay_hours', 'check_out_hour']);
        });
        Schema::dropIfExists('microstay_rates');
    }
};
