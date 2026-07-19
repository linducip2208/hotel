<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Out-of-order period (room maintenance/blocked)
        Schema::create('out_of_order_periods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained();
            $t->date('from_date');
            $t->date('to_date');
            $t->string('reason'); // maintenance|renovation|deep_clean|damage|other
            $t->text('description')->nullable();
            $t->foreignId('work_order_id')->nullable()->constrained();
            $t->foreignId('created_by_user_id')->nullable()->constrained('users');
            $t->string('status')->default('active'); // active|cleared|cancelled
            $t->timestamp('cleared_at')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'from_date', 'to_date']);
        });

        // Allotment block per travel agent / company
        Schema::create('allotments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('travel_agent_id')->nullable()->constrained();
            $t->foreignId('company_id')->nullable()->constrained();
            $t->foreignId('room_type_id')->constrained();
            $t->foreignId('rate_plan_id')->nullable()->constrained();
            $t->date('from_date');
            $t->date('to_date');
            $t->unsignedSmallInteger('rooms_blocked');
            $t->unsignedSmallInteger('rooms_picked_up')->default(0);
            $t->date('release_date')->nullable(); // setelah ini, allotment auto-release ke umum
            $t->decimal('negotiated_rate', 12, 2)->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
        });

        // Daily flash report aggregate
        Schema::create('daily_flash_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->date('report_date');
            $t->json('rooms_kpi'); // occupancy_pct, sold, complimentary, oo, available, ADR, RevPAR
            $t->json('revenue_breakdown'); // room, fnb, minibar, laundry, spa, other
            $t->json('tax_breakdown'); // pb1, ppn, service_charge
            $t->json('payment_breakdown'); // cash, card, qris, transfer, ota, city_ledger
            $t->json('source_mix'); // direct, ota:booking, ota:agoda, walkin, ...
            $t->decimal('total_revenue', 14, 2);
            $t->timestamps();
            $t->unique(['property_id', 'report_date']);
        });

        // Recipe / BOM untuk POS menu items
        Schema::create('pos_recipes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('menu_item_id')->constrained('pos_menu_items')->cascadeOnDelete();
            $t->foreignId('stock_item_id')->constrained('stock_items');
            $t->decimal('qty_per_serving', 14, 4);
            $t->string('unit')->nullable();
            $t->timestamps();
            $t->unique(['menu_item_id', 'stock_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_recipes');
        Schema::dropIfExists('daily_flash_reports');
        Schema::dropIfExists('allotments');
        Schema::dropIfExists('out_of_order_periods');
    }
};
