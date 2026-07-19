<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $t) {
            if (!Schema::hasColumn('reviews', 'source')) {
                $t->string('source')->default('direct')->after('property_id');
            }
            if (!Schema::hasColumn('reviews', 'external_id')) {
                $t->string('external_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('reviews', 'author_name')) {
                $t->string('author_name')->nullable()->after('guest_id');
            }
            if (!Schema::hasColumn('reviews', 'reviewed_at')) {
                $t->timestamp('reviewed_at')->nullable()->after('comment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $t) {
            $t->dropColumn(['source', 'external_id', 'author_name', 'reviewed_at']);
        });
    }
};
