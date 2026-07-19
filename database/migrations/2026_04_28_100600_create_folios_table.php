<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('folios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained();
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->foreignId('company_id')->nullable()->constrained();
            $t->string('folio_no')->index();
            $t->string('type')->default('guest'); // guest|master|city_ledger|house
            $t->string('status')->default('open'); // open|closed|void
            $t->decimal('total_charges', 14, 2)->default(0);
            $t->decimal('total_payments', 14, 2)->default(0);
            $t->decimal('balance', 14, 2)->default(0);
            $t->string('currency', 3)->default('IDR');
            $t->timestamp('opened_at')->useCurrent();
            $t->timestamp('closed_at')->nullable();
            $t->foreignId('cashier_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['property_id', 'folio_no']);
        });

        Schema::create('folio_charges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('folio_id')->constrained()->cascadeOnDelete();
            $t->foreignId('property_id')->constrained();
            $t->date('charge_date');
            $t->string('description');
            $t->string('category')->index(); // room|fnb|minibar|laundry|spa|service_charge|pb1|ppn|addon|other|discount
            $t->unsignedInteger('qty')->default(1);
            $t->decimal('unit_price', 12, 2)->default(0);
            $t->decimal('amount', 14, 2);
            $t->string('tax_code')->nullable(); // PB1|PPN_OUT|PPH23
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->boolean('is_taxable')->default(true);
            $t->boolean('is_void')->default(false);
            $t->text('void_reason')->nullable();
            $t->foreignId('source_type_id')->nullable();
            $t->string('source_type')->nullable(); // pos_order|reservation|night_audit|manual
            $t->string('source_ref')->nullable();
            $t->foreignId('posted_by_user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->index(['folio_id', 'charge_date']);
        });

        Schema::create('folio_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('folio_id')->constrained()->cascadeOnDelete();
            $t->foreignId('property_id')->constrained();
            $t->date('payment_date');
            $t->decimal('amount', 14, 2);
            $t->string('method'); // cash|card|qris|transfer|voucher|deposit|ota_settle|company_charge
            $t->string('provider_id')->nullable();
            $t->string('reference_no')->nullable();
            $t->decimal('mdr_amount', 14, 2)->default(0);
            $t->json('gateway_payload')->nullable();
            $t->boolean('is_void')->default(false);
            $t->text('void_reason')->nullable();
            $t->foreignId('cashier_id')->nullable()->constrained('users');
            $t->foreignId('shift_id')->nullable();
            $t->timestamps();
            $t->index(['folio_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_payments');
        Schema::dropIfExists('folio_charges');
        Schema::dropIfExists('folios');
    }
};
