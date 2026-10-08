<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $t) {
            // unpaid|pending|paid|partial|failed|refunded
            $t->string('payment_status', 20)->default('unpaid')->after('status')->index();
        });

        // Backfill from folio settlement state.
        DB::statement("
            UPDATE reservations
            SET payment_status = 'paid'
            WHERE EXISTS (
                SELECT 1 FROM folios
                WHERE folios.reservation_id = reservations.id
                  AND folios.deleted_at IS NULL
                  AND folios.status = 'closed'
            )
        ");
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $t) {
            $t->dropColumn('payment_status');
        });
    }
};
