<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folio_payments', function (Blueprint $t) {
            // Payment lifecycle: pending → paid | pending → failed.
            // Legacy/manual payments are recorded after money is received => 'paid'.
            $t->string('status', 20)->default('paid')->after('method')->index();
        });

        // Backfill: existing rows were all recorded settled payments.
        DB::table('folio_payments')->whereNull('status')->update(['status' => 'paid']);

        Schema::table('folio_payments', function (Blueprint $t) {
            // Idempotency: one provider transaction reference per property.
            $t->unique(['property_id', 'reference_no'], 'folio_payments_property_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('folio_payments', function (Blueprint $t) {
            $t->dropUnique('folio_payments_property_reference_unique');
        });
        Schema::table('folio_payments', function (Blueprint $t) {
            $t->dropColumn('status');
        });
    }
};
