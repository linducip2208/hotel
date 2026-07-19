<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_blocks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('company_id')->nullable()->constrained();
            $t->string('block_code')->unique();
            $t->string('group_name');
            $t->date('check_in');
            $t->date('check_out');
            $t->unsignedSmallInteger('rooms_count');
            $t->decimal('negotiated_rate', 12, 2)->nullable();
            $t->date('cutoff_date')->nullable();
            $t->string('status')->default('tentative'); // tentative|definite|cancelled|completed
            $t->foreignId('master_folio_id')->nullable()->constrained('folios');
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('group_block_rooms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('group_block_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_type_id')->constrained();
            $t->unsignedSmallInteger('rooms_count');
            $t->unsignedSmallInteger('rooms_picked_up')->default(0);
            $t->decimal('rate', 12, 2)->nullable();
            $t->timestamps();
        });

        Schema::create('waitlist_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained();
            $t->string('first_name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->date('check_in');
            $t->date('check_out');
            $t->unsignedSmallInteger('rooms')->default(1);
            $t->foreignId('preferred_room_type_id')->nullable()->constrained('room_types');
            $t->string('status')->default('waiting'); // waiting|notified|converted|expired|cancelled
            $t->timestamp('notified_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_entries');
        Schema::dropIfExists('group_block_rooms');
        Schema::dropIfExists('group_blocks');
    }
};
