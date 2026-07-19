<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deployment_heartbeats', function (Blueprint $t) {
            $t->id();
            $t->string('license_key_hash')->index();
            $t->string('deployment_id')->nullable()->index();
            $t->string('version')->nullable();
            $t->unsignedInteger('rooms_count')->nullable();
            $t->unsignedInteger('active_bookings')->nullable();
            $t->unsignedInteger('queue_jobs_pending')->nullable();
            $t->unsignedInteger('queue_jobs_failed_24h')->nullable();
            $t->unsignedInteger('errors_24h')->nullable();
            $t->unsignedInteger('db_size_mb')->nullable();
            $t->decimal('uptime_pct_24h', 5, 2)->nullable();
            $t->timestamp('received_at')->index();
            $t->string('source_ip', 45)->nullable();
            $t->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_heartbeats');
    }
};
