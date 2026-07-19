<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chargebacks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('folio_charge_id')->nullable()->constrained('folio_charges')->nullOnDelete();
            $t->foreignId('payment_transaction_id')->nullable()->constrained('folio_payments')->nullOnDelete();
            $t->date('chargeback_date');
            $t->decimal('amount', 14, 2)->default(0);
            $t->string('reason_code')->nullable();
            $t->text('reason_description')->nullable();
            $t->string('card_brand')->nullable();
            $t->string('card_last_4', 4)->nullable();
            $t->string('status')->default('open');
            $t->string('disputed_by')->nullable();
            $t->date('evidence_deadline')->nullable();
            $t->timestamp('response_submitted_at')->nullable();
            $t->string('final_decision')->nullable();
            $t->date('final_decision_date')->nullable();
            $t->decimal('recovered_amount', 14, 2)->default(0);
            $t->text('internal_notes')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'status']);
        });

        Schema::create('chargeback_evidence', function (Blueprint $t) {
            $t->id();
            $t->foreignId('chargeback_id')->constrained()->cascadeOnDelete();
            $t->string('evidence_type')->default('other');
            $t->string('file_path');
            $t->text('description')->nullable();
            $t->timestamp('uploaded_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chargeback_evidence');
        Schema::dropIfExists('chargebacks');
    }
};
