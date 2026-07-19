<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linen_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type'); // bed_sheet|pillow_case|towel|bathrobe|blanket|table_cloth
            $t->unsignedInteger('initial_stock')->default(0);
            $t->unsignedInteger('current_stock')->default(0);
            $t->unsignedInteger('damaged')->default(0);
            $t->timestamp('last_audit_at')->nullable();
            $t->timestamps();
        });

        Schema::create('linen_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('linen_item_id')->constrained('linen_items')->cascadeOnDelete();
            $t->string('type'); // in|out|damaged|discarded
            $t->unsignedInteger('quantity');
            $t->string('reference')->nullable();
            $t->foreignId('staff_id')->nullable()->constrained('users');
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linen_transactions');
        Schema::dropIfExists('linen_items');
    }
};
