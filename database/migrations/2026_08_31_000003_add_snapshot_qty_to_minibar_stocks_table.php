<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('minibar_stocks', function (Blueprint $t) {
            // Stock level captured when a guest checks in. Checkout auto-charge
            // compares against this snapshot so consumption recorded during the
            // stay is never charged twice.
            $t->unsignedSmallInteger('snapshot_qty')->nullable()->after('current_qty');
        });
    }

    public function down(): void
    {
        Schema::table('minibar_stocks', function (Blueprint $t) {
            $t->dropColumn('snapshot_qty');
        });
    }
};
