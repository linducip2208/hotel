<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $t->string('deposit_type')->default('incidental');
            $t->decimal('amount', 14, 2)->default(0);
            $t->string('payment_method')->nullable();
            $t->string('payment_reference')->nullable();
            $t->date('received_date');
            $t->decimal('refunded_amount', 14, 2)->default(0);
            $t->date('refund_date')->nullable();
            $t->string('refund_method')->nullable();
            $t->string('status')->default('held');
            $t->foreignId('folio_charge_id')->nullable()->constrained('folio_charges')->nullOnDelete();
            $t->text('forfeiture_reason')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by_user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->index(['property_id', 'status']);
            $t->index(['property_id', 'reservation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
