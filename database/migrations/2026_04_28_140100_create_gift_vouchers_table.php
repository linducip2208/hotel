<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gift_vouchers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('code', 32)->unique();
            $t->string('type'); // amount|night|package|spa|fnb
            $t->decimal('face_value', 14, 2);
            $t->decimal('balance', 14, 2);
            $t->string('currency', 3)->default('IDR');
            $t->date('valid_from')->nullable();
            $t->date('valid_until')->nullable();
            $t->foreignId('issued_to_guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $t->string('issued_to_email')->nullable();
            $t->string('issued_to_phone')->nullable();
            $t->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('purchased_via_folio_id')->nullable()->constrained('folios')->nullOnDelete();
            $t->string('status')->default('active'); // active|partially_redeemed|fully_redeemed|expired|cancelled
            $t->text('message')->nullable();
            $t->timestamp('issued_at')->useCurrent();
            $t->timestamps();
        });

        Schema::create('voucher_redemptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('voucher_id')->constrained('gift_vouchers')->cascadeOnDelete();
            $t->foreignId('folio_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('amount', 14, 2);
            $t->timestamp('redeemed_at')->useCurrent();
            $t->foreignId('redeemed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('gift_vouchers');
    }
};
