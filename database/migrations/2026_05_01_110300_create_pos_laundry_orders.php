<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_laundry_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained('guests');
            $t->foreignId('reservation_id')->nullable()->constrained('reservations');
            $t->foreignId('room_id')->nullable()->constrained('rooms');
            $t->string('order_number')->unique();
            $t->string('status')->default('received'); // received|washing|drying|folding|ready|delivered
            $t->json('items');
            $t->decimal('total_amount', 14, 2)->default(0);
            $t->string('payment_status')->default('unpaid'); // unpaid|charged_to_room|paid
            $t->text('notes')->nullable();
            $t->foreignId('received_by')->nullable()->constrained('users');
            $t->foreignId('delivered_by')->nullable()->constrained('users');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_laundry_orders');
    }
};
