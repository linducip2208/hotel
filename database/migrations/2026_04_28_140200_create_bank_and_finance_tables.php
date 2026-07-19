<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('coa_account_id')->constrained('chart_of_accounts');
            $t->string('bank_name');
            $t->string('account_no');
            $t->string('account_holder');
            $t->string('currency', 3)->default('IDR');
            $t->string('swift_code')->nullable();
            $t->boolean('is_active')->default(true);
            $t->boolean('is_primary')->default(false);
            $t->timestamps();
        });

        Schema::create('bank_statements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $t->date('statement_date');
            $t->date('period_from');
            $t->date('period_to');
            $t->decimal('opening_balance', 16, 2);
            $t->decimal('closing_balance', 16, 2);
            $t->string('source_file')->nullable();
            $t->string('status')->default('imported'); // imported|reconciling|reconciled
            $t->timestamps();
        });

        Schema::create('bank_statement_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('statement_id')->constrained('bank_statements')->cascadeOnDelete();
            $t->date('transaction_date');
            $t->string('description');
            $t->decimal('debit', 16, 2)->default(0);
            $t->decimal('credit', 16, 2)->default(0);
            $t->decimal('balance', 16, 2)->nullable();
            $t->string('reference_no')->nullable();
            $t->foreignId('matched_journal_line_id')->nullable()->constrained('journal_lines')->nullOnDelete();
            $t->boolean('is_reconciled')->default(false);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('budget_periods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('year');
            $t->string('name')->nullable();
            $t->string('status')->default('draft'); // draft|active|closed
            $t->timestamps();
            $t->unique(['property_id', 'year']);
        });

        Schema::create('budget_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('budget_period_id')->constrained('budget_periods')->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('chart_of_accounts');
            $t->unsignedTinyInteger('month');
            $t->decimal('amount', 14, 2);
            $t->timestamps();
            $t->unique(['budget_period_id', 'account_id', 'month']);
        });

        Schema::create('owner_statements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->nullable()->constrained()->nullOnDelete(); // for villa-titip
            $t->string('owner_name');
            $t->string('owner_email')->nullable();
            $t->unsignedSmallInteger('year');
            $t->unsignedTinyInteger('month');
            $t->decimal('gross_revenue', 14, 2);
            $t->decimal('mgmt_fee_pct', 6, 3)->default(20);
            $t->decimal('mgmt_fee_amount', 14, 2);
            $t->decimal('expenses_total', 14, 2)->default(0);
            $t->decimal('net_payable_to_owner', 14, 2);
            $t->json('breakdown')->nullable();
            $t->string('status')->default('draft'); // draft|sent|paid
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'room_id', 'year', 'month']);
        });

        Schema::create('fx_rates', function (Blueprint $t) {
            $t->id();
            $t->string('base_currency', 3);
            $t->string('quote_currency', 3);
            $t->date('rate_date');
            $t->decimal('rate', 18, 8);
            $t->string('source')->default('manual'); // manual|api|bi (Bank Indonesia)
            $t->timestamps();
            $t->unique(['base_currency', 'quote_currency', 'rate_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_rates');
        Schema::dropIfExists('owner_statements');
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('budget_periods');
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('bank_accounts');
    }
};
