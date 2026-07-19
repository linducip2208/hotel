<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('pr_number')->unique();
            $t->foreignId('requested_by')->constrained('users');
            $t->string('department')->nullable();
            $t->date('required_date')->nullable();
            $t->string('status')->default('draft'); // draft|pending|approved|rejected|ordered
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('purchase_request_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pr_id')->constrained('purchase_requests')->cascadeOnDelete();
            $t->foreignId('stock_item_id')->nullable()->constrained()->nullOnDelete();
            $t->string('description');
            $t->decimal('quantity', 14, 3);
            $t->string('unit')->default('pcs');
            $t->decimal('estimated_price', 14, 2)->nullable();
            $t->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('po_number')->unique();
            $t->foreignId('vendor_id')->constrained('ap_suppliers');
            $t->foreignId('pr_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $t->foreignId('ordered_by')->constrained('users');
            $t->date('order_date');
            $t->date('expected_date')->nullable();
            $t->string('status')->default('draft'); // draft|sent|partial|received|cancelled
            $t->decimal('total', 14, 2);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('purchase_order_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $t->foreignId('stock_item_id')->nullable()->constrained()->nullOnDelete();
            $t->string('description');
            $t->decimal('quantity', 14, 3);
            $t->decimal('unit_price', 12, 2);
            $t->decimal('total', 14, 2);
            $t->timestamps();
        });

        Schema::create('goods_receipts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('gr_number')->unique();
            $t->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $t->foreignId('received_by')->constrained('users');
            $t->date('received_date');
            $t->string('status')->default('pending'); // pending|accepted|rejected
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('goods_receipt_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gr_id')->constrained('goods_receipts')->cascadeOnDelete();
            $t->foreignId('stock_item_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('quantity_received', 14, 3);
            $t->decimal('quantity_accepted', 14, 3)->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_lines');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_request_lines');
        Schema::dropIfExists('purchase_requests');
    }
};
