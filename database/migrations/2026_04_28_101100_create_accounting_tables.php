<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('code', 16);
            $t->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $t->string('name');
            $t->string('type'); // asset|liability|equity|revenue|expense|header
            $t->string('normal_balance'); // debit|credit
            $t->string('description')->nullable();
            $t->boolean('is_system')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'code']);
        });

        Schema::create('accounting_periods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('year');
            $t->unsignedTinyInteger('month');
            $t->string('status')->default('open'); // open|locked
            $t->timestamp('locked_at')->nullable();
            $t->foreignId('locked_by_user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->unique(['property_id', 'year', 'month']);
        });

        Schema::create('journal_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('entry_no');
            $t->date('posted_at');
            $t->unsignedSmallInteger('period_year');
            $t->unsignedTinyInteger('period_month');
            $t->string('description');
            $t->string('source_type')->nullable();
            $t->unsignedBigInteger('source_id')->nullable();
            $t->decimal('total_debit', 16, 2);
            $t->decimal('total_credit', 16, 2);
            $t->string('status')->default('posted'); // draft|posted|void
            $t->foreignId('created_by_user_id')->nullable()->constrained('users');
            $t->foreignId('posted_by_user_id')->nullable()->constrained('users');
            $t->foreignId('voided_by_user_id')->nullable()->constrained('users');
            $t->timestamp('voided_at')->nullable();
            $t->text('void_reason')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'entry_no']);
            $t->index(['property_id', 'period_year', 'period_month']);
            $t->index(['source_type', 'source_id']);
        });

        Schema::create('journal_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('chart_of_accounts');
            $t->string('description')->nullable();
            $t->decimal('debit', 16, 2)->default(0);
            $t->decimal('credit', 16, 2)->default(0);
            $t->string('tax_code')->nullable();
            $t->unsignedSmallInteger('line_no');
            $t->timestamps();
            $t->index('account_id');
        });

        Schema::create('ar_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('account_type'); // city_ledger|ota|guest
            $t->foreignId('company_id')->nullable()->constrained();
            $t->foreignId('travel_agent_id')->nullable()->constrained();
            $t->foreignId('channel_id')->nullable()->constrained();
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->decimal('credit_limit', 14, 2)->default(0);
            $t->decimal('balance_cached', 14, 2)->default(0);
            $t->unsignedSmallInteger('payment_terms_days')->default(30);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('ar_invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ar_account_id')->constrained();
            $t->string('invoice_no');
            $t->date('issued_at');
            $t->date('due_at');
            $t->decimal('subtotal', 14, 2);
            $t->decimal('tax_total', 14, 2)->default(0);
            $t->decimal('grand_total', 14, 2);
            $t->decimal('paid_total', 14, 2)->default(0);
            $t->decimal('balance', 14, 2);
            $t->string('status')->default('open'); // open|partial|paid|overdue|void
            $t->json('attachments')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['property_id', 'invoice_no']);
        });

        Schema::create('ar_invoice_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained('ar_invoices')->cascadeOnDelete();
            $t->string('description');
            $t->decimal('qty', 10, 2)->default(1);
            $t->decimal('unit_price', 12, 2);
            $t->decimal('amount', 14, 2);
            $t->string('tax_code')->nullable();
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('ar_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained('ar_invoices')->cascadeOnDelete();
            $t->date('paid_at');
            $t->decimal('amount', 14, 2);
            $t->string('method');
            $t->string('reference_no')->nullable();
            $t->foreignId('journal_entry_id')->nullable()->constrained();
            $t->timestamps();
        });

        Schema::create('ap_suppliers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('legal_name')->nullable();
            $t->string('npwp')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('address_line1')->nullable();
            $t->boolean('subject_pph23')->default(false);
            $t->decimal('pph23_rate', 6, 3)->default(2.000);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('ap_bills', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('supplier_id')->constrained('ap_suppliers');
            $t->string('bill_no');
            $t->string('vendor_invoice_no')->nullable();
            $t->date('issued_at');
            $t->date('due_at');
            $t->decimal('subtotal', 14, 2);
            $t->decimal('tax_total', 14, 2)->default(0);
            $t->decimal('withholding_total', 14, 2)->default(0);
            $t->decimal('grand_total', 14, 2);
            $t->decimal('paid_total', 14, 2)->default(0);
            $t->decimal('balance', 14, 2);
            $t->string('status')->default('open');
            $t->json('attachments')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['property_id', 'bill_no']);
        });

        Schema::create('ap_bill_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bill_id')->constrained('ap_bills')->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('chart_of_accounts');
            $t->string('description');
            $t->decimal('qty', 10, 2)->default(1);
            $t->decimal('unit_price', 12, 2);
            $t->decimal('amount', 14, 2);
            $t->string('tax_code')->nullable();
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->decimal('withholding_amount', 14, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('ap_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bill_id')->constrained('ap_bills')->cascadeOnDelete();
            $t->date('paid_at');
            $t->decimal('amount', 14, 2);
            $t->string('method');
            $t->string('reference_no')->nullable();
            $t->foreignId('journal_entry_id')->nullable()->constrained();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_payments');
        Schema::dropIfExists('ap_bill_lines');
        Schema::dropIfExists('ap_bills');
        Schema::dropIfExists('ap_suppliers');
        Schema::dropIfExists('ar_payments');
        Schema::dropIfExists('ar_invoice_lines');
        Schema::dropIfExists('ar_invoices');
        Schema::dropIfExists('ar_accounts');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('chart_of_accounts');
    }
};
