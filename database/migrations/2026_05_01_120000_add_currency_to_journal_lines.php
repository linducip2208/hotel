<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('journal_lines', function (Blueprint $t) {
            $t->decimal('original_amount', 16, 2)->nullable()->after('credit');
            $t->string('currency', 3)->nullable()->after('original_amount')->comment('NULL = base currency (IDR)');
        });
    }

    public function down(): void
    {
        Schema::table('journal_lines', function (Blueprint $t) {
            $t->dropColumn(['original_amount', 'currency']);
        });
    }
};
