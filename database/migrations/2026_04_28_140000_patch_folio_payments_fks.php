<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('folio_payments', function (Blueprint $t) {
            // Drop string column dan ganti jadi proper FK ke providers
            $t->dropColumn('provider_id');
        });
        Schema::table('folio_payments', function (Blueprint $t) {
            $t->foreignId('provider_id')->nullable()->after('method')->constrained('providers')->nullOnDelete();
            $t->foreign('shift_id')->references('id')->on('cashier_shifts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('folio_payments', function (Blueprint $t) {
            $t->dropForeign(['provider_id']);
            $t->dropForeign(['shift_id']);
            $t->dropColumn('provider_id');
        });
        Schema::table('folio_payments', function (Blueprint $t) {
            $t->string('provider_id')->nullable()->after('method');
        });
    }
};
