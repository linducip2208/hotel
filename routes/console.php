<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('license:heartbeat')->dailyAt('03:00');
Schedule::command('license:heartbeat-retry')->everyFourHours();

Schedule::command('night-audit:close')->dailyAt('23:55');
Schedule::command('channel:sync-ari')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('channel:fetch-bookings')->everyTenMinutes()->withoutOverlapping();

Schedule::command('seo:rebuild-sitemap')->dailyAt('02:00');
Schedule::command('seo:regenerate-stale')->dailyAt('02:30');
Schedule::command('seo:indexnow')->dailyAt('02:45')->withoutOverlapping();

Schedule::command('accounting:export-daily')->dailyAt('06:00');

Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('queue:prune-failed --hours=72')->daily();

Schedule::command('telemetry:push')->hourly();

Schedule::command('audit:checkpoint')->dailyAt('01:30');
Schedule::command('audit:verify-chain')->weeklyOn(0, '02:30');

Schedule::command('tenant:lifecycle')->dailyAt('04:00');

// Open Pricing & Dynamic Pricing
Schedule::command('pricing:apply-dynamic-rules')->dailyAt('00:30')->withoutOverlapping();

// Channel Parity Monitoring
Schedule::command('parity:check')->hourly()->withoutOverlapping();

// Guest Intelligence
Schedule::command('guests:rebuild-profiles')->dailyAt('03:30')->withoutOverlapping();

// Check-in Reminders (D-1)
Schedule::command('notifications:checkin-reminders')->dailyAt('09:00')->withoutOverlapping();

// Spa Membership Renewals
Schedule::command('spa:renew-memberships')->dailyAt('08:00');

// Competitor Rate Shopping
Schedule::command('rates:fetch-competitor')->hourly();

Schedule::command('hotel:send-birthday-greetings')->dailyAt('08:00');
Schedule::command('hotel:backup-database')->dailyAt('03:00');
Schedule::command('hotel:escalate-overdue')->hourly();
Schedule::command('hotel:send-notifications')->everyFiveMinutes();
Schedule::command('hotel:send-reminders')->dailyAt('09:00');

// Drip Campaign processing
Schedule::command('drip:process')->everyFiveMinutes();

// HK Auto-Assign
Schedule::command('hk:auto-assign')->everyThirtyMinutes();

// Preventive Maintenance
Schedule::command('pm:check-due')->dailyAt('07:00');

// Compliance — License Expiry Check
Schedule::command('compliance:check-licenses')->dailyAt('08:00');

// Group Block auto-release
Schedule::command('groups:release-expired')->dailyAt('02:00');

// ════════════════ 8 ENHANCEMENTS SCHEDULER ════════════════
Schedule::command('hotel:expire-microstays')->everyTenMinutes();
Schedule::command('hotel:calculate-rfm')->dailyAt('04:30')->withoutOverlapping();
Schedule::command('hotel:upsell-campaigns')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('hotel:scrape-rates')->everySixHours()->withoutOverlapping();
