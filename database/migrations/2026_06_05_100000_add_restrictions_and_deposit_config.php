<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('channel_room_mappings', function (Blueprint $t) {
            $t->json('restrictions')->nullable()->after('config');
        });

        Schema::table('rate_plans', function (Blueprint $t) {
            $t->json('deposit_config')->nullable()->after('cancellation_policy');
        });
    }

    public function down(): void
    {
        Schema::table('channel_room_mappings', function (Blueprint $t) {
            $t->dropColumn('restrictions');
        });

        Schema::table('rate_plans', function (Blueprint $t) {
            $t->dropColumn('deposit_config');
        });
    }
};
