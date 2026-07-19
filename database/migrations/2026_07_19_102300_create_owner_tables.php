<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_owners', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->decimal('ownership_pct', 5, 2)->default(100.00);
            $t->decimal('investment_amount', 14, 2)->default(0);
            $t->date('joined_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'user_id']);
        });

        Schema::create('owner_distributions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $t->date('period_start');
            $t->date('period_end');
            $t->decimal('total_revenue', 14, 2)->default(0);
            $t->decimal('total_expense', 14, 2)->default(0);
            $t->decimal('net_profit', 14, 2)->default(0);
            $t->decimal('distribution_amount', 14, 2)->default(0);
            $t->decimal('distribution_pct', 5, 2)->default(100.00);
            $t->string('status')->default('pending');
            $t->timestamp('paid_at')->nullable();
            $t->string('payment_method')->nullable();
            $t->string('reference_number')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by_user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->index(['property_id', 'period_start', 'period_end']);
        });

        Schema::create('owner_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $t->string('title');
            $t->string('document_type');
            $t->string('file_path');
            $t->timestamp('uploaded_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_documents');
        Schema::dropIfExists('owner_distributions');
        Schema::dropIfExists('property_owners');
    }
};
