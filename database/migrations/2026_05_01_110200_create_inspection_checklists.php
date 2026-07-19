<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inspection_checklists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained();
            $t->foreignId('inspector_id')->nullable()->constrained('users');
            $t->timestamp('inspected_at')->nullable();
            $t->string('overall_status')->default('pending'); // pass|fail|pending
            $t->json('items');
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_checklists');
    }
};
