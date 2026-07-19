<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('category');
            $t->string('contact_person')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->text('address')->nullable();
            $t->string('tax_id')->nullable();
            $t->integer('payment_terms_days')->default(30);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('vendor_contracts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $t->string('contract_number')->nullable();
            $t->date('start_date');
            $t->date('end_date')->nullable();
            $t->decimal('value', 14, 2)->default(0);
            $t->string('status')->default('active');
            $t->text('scope_of_work')->nullable();
            $t->string('document_path')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_contracts');
        Schema::dropIfExists('vendors');
    }
};
