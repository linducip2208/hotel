<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_outlets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code');
            $t->string('type')->default('restaurant'); // restaurant|bar|spa|minibar|other
            $t->boolean('charge_to_room_enabled')->default(true);
            $t->boolean('takeaway_enabled')->default(false);
            $t->json('config')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['property_id', 'code']);
        });

        Schema::create('pos_tables', function (Blueprint $t) {
            $t->id();
            $t->foreignId('outlet_id')->constrained('pos_outlets')->cascadeOnDelete();
            $t->string('label'); // e.g. T1, A2, Bar-3
            $t->unsignedTinyInteger('seats')->default(2);
            $t->string('status')->default('available'); // available|occupied|reserved
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('pos_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('outlet_id')->constrained('pos_outlets')->cascadeOnDelete();
            $t->string('name');
            $t->unsignedSmallInteger('display_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('pos_menu_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('outlet_id')->constrained('pos_outlets')->cascadeOnDelete();
            $t->foreignId('category_id')->nullable()->constrained('pos_categories')->nullOnDelete();
            $t->string('code');
            $t->string('name');
            $t->text('description')->nullable();
            $t->decimal('price', 12, 2);
            $t->boolean('is_taxable')->default(true);
            $t->string('tax_code')->default('PPN_OUT');
            $t->decimal('cogs', 12, 2)->nullable();
            $t->json('modifiers')->nullable();
            $t->json('photos')->nullable();
            $t->boolean('is_available')->default(true);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['outlet_id', 'code']);
        });

        Schema::create('pos_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('outlet_id')->constrained('pos_outlets')->cascadeOnDelete();
            $t->foreignId('property_id')->constrained();
            $t->foreignId('table_id')->nullable()->constrained('pos_tables');
            $t->string('order_no')->index();
            $t->string('type')->default('dine_in'); // dine_in|room_service|takeaway
            $t->foreignId('folio_id')->nullable()->constrained('folios');
            $t->foreignId('reservation_id')->nullable()->constrained('reservations');
            $t->foreignId('server_id')->nullable()->constrained('users');
            $t->string('status')->default('open'); // open|sent|served|settled|void
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('discount', 14, 2)->default(0);
            $t->decimal('service_charge', 14, 2)->default(0);
            $t->decimal('tax_total', 14, 2)->default(0);
            $t->decimal('grand_total', 14, 2)->default(0);
            $t->decimal('paid_total', 14, 2)->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('pos_order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained('pos_orders')->cascadeOnDelete();
            $t->foreignId('menu_item_id')->constrained('pos_menu_items');
            $t->string('name'); // snapshot
            $t->decimal('unit_price', 12, 2);
            $t->unsignedInteger('qty')->default(1);
            $t->json('modifiers')->nullable();
            $t->decimal('subtotal', 14, 2);
            $t->boolean('sent_to_kitchen')->default(false);
            $t->timestamp('sent_at')->nullable();
            $t->boolean('is_void')->default(false);
            $t->text('void_reason')->nullable();
            $t->timestamps();
        });

        Schema::create('pos_order_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained('pos_orders')->cascadeOnDelete();
            $t->decimal('amount', 14, 2);
            $t->string('method');
            $t->string('reference_no')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_payments');
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
        Schema::dropIfExists('pos_menu_items');
        Schema::dropIfExists('pos_categories');
        Schema::dropIfExists('pos_tables');
        Schema::dropIfExists('pos_outlets');
    }
};
