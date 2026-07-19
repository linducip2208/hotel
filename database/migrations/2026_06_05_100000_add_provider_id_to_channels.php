<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $t) {
            $t->foreignId('provider_id')->nullable()->after('property_id')->constrained('providers')->nullOnDelete();
            $t->string('api_format')->nullable()->after('code');
        });

        Schema::table('channels', function (Blueprint $t) {
            $t->json('settings')->nullable()->after('config');
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $t) {
            $t->dropForeign(['provider_id']);
            $t->dropColumn('provider_id');
            $t->dropColumn('api_format');
            $t->dropColumn('settings');
        });
    }
};
