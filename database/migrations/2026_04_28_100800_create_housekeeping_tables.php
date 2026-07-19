<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hk_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained();
            $t->string('type'); // cleaning|deep_clean|inspection|maintenance|turn_down
            $t->string('priority')->default('normal');
            $t->string('status')->default('pending'); // pending|in_progress|done|skipped
            $t->foreignId('assignee_id')->nullable()->constrained('users');
            $t->date('scheduled_date');
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->unsignedSmallInteger('duration_minutes')->nullable();
            $t->text('notes')->nullable();
            $t->json('photos')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'scheduled_date', 'status']);
        });

        Schema::create('lost_and_found', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->nullable()->constrained();
            $t->string('description');
            $t->string('photo_path')->nullable();
            $t->string('found_location')->nullable();
            $t->date('found_date');
            $t->foreignId('found_by_user_id')->nullable()->constrained('users');
            $t->string('status')->default('stored'); // stored|claimed|disposed
            $t->foreignId('claimed_by_guest_id')->nullable()->constrained('guests');
            $t->date('claimed_date')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_and_found');
        Schema::dropIfExists('hk_tasks');
    }
};
