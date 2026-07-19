<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel parity monitoring: detect when an OTA sells your room cheaper than
 * your direct rate (rate parity breach) and log alerts for action.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('channel_parity_alerts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained();
            $t->foreignId('channel_id')->nullable()->constrained()->nullOnDelete();
            $t->date('check_date');
            $t->decimal('direct_rate', 14, 2);       // your own booking engine rate
            $t->decimal('channel_rate', 14, 2);      // what the OTA is showing
            $t->decimal('gap_amount', 14, 2);        // direct - channel (negative = breach)
            $t->decimal('gap_pct', 8, 4);            // gap / direct * 100
            $t->string('severity')->default('low');  // low|medium|high|critical
            $t->string('status')->default('open');   // open|acknowledged|resolved|ignored
            $t->string('action_taken')->nullable();  // rate_adjusted|stop_sell|ignored
            $t->foreignId('resolved_by_user_id')->nullable()->constrained('users');
            $t->timestamp('resolved_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'status', 'check_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_parity_alerts');
    }
};
