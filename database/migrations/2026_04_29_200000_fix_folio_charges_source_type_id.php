<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * folio_charges.source_type_id was created with foreignId() which adds a
 * NOT NULL unsigned bigint with an implicit FK constraint pointing to a
 * non-existent table. It should be a plain unsignedBigInteger nullable for
 * polymorphic morphTo usage (source_type + source_id pair).
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            Schema::table('folio_charges', function (Blueprint $t) {
                $t->dropForeign(['source_type_id']);
            });
        } catch (\Throwable) {
            // FK may not exist on all DB engines — safe to ignore
        }

        Schema::table('folio_charges', function (Blueprint $t) {
            $t->unsignedBigInteger('source_type_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('folio_charges', function (Blueprint $t) {
            $t->foreignId('source_type_id')->nullable()->change();
        });
    }
};
