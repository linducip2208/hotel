<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('iot_devices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->string('device_type');
            $t->string('name');
            $t->string('device_id');
            $t->string('status')->default('online');
            $t->json('current_state')->nullable();
            $t->json('config')->nullable();
            $t->timestamp('last_heartbeat_at')->nullable();
            $t->timestamps();
        });

        Schema::create('iot_commands', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('iot_device_id')->constrained()->cascadeOnDelete();
            $t->string('command');
            $t->json('payload')->nullable();
            $t->string('status')->default('pending');
            $t->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('trigger')->default('manual');
            $t->timestamps();
        });

        Schema::create('iot_energy_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->foreignId('iot_device_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('energy_kwh', 10, 3);
            $t->decimal('cost_estimate', 14, 2);
            $t->date('log_date');
            $t->timestamps();
            $t->index(['property_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_energy_logs');
        Schema::dropIfExists('iot_commands');
        Schema::dropIfExists('iot_devices');
    }
};
