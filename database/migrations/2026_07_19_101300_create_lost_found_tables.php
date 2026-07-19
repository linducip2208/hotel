<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lost_found_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('item_number')->unique();
            $t->string('name');
            $t->string('category'); // electronics|clothing|jewelry|documents|toys|keys|other
            $t->text('description')->nullable();
            $t->string('location_found')->nullable();
            $t->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('found_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('found_at')->useCurrent();
            $t->string('status')->default('found'); // found|claimed|disposed|donated|returned
            $t->timestamp('claimed_at')->nullable();
            $t->foreignId('claimed_by_guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $t->string('claim_verified_by')->nullable();
            $t->text('storage_location')->nullable();
            $t->json('photos')->nullable();
            $t->integer('disposal_days')->default(90);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_found_items');
    }
};
