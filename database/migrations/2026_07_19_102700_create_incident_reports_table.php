<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('report_number')->unique();
            $t->string('incident_type'); // guest_injury|guest_illness|theft|property_damage|staff_injury|security|fire|flood|complaint|other
            $t->string('severity')->default('medium'); // low|medium|high|critical
            $t->string('location')->nullable();
            $t->datetime('incident_date');
            $t->string('reported_by')->nullable();
            $t->foreignId('reported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $t->text('description')->nullable();
            $t->text('immediate_actions')->nullable();
            $t->string('witness_name')->nullable();
            $t->string('witness_contact')->nullable();
            $t->string('status')->default('open'); // open|investigating|resolved|closed
            $t->text('resolution')->nullable();
            $t->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->datetime('resolved_at')->nullable();
            $t->boolean('police_report_filed')->default(false);
            $t->boolean('insurance_claim_filed')->default(false);
            $t->json('photos')->nullable();
            $t->timestamps();
        });

        Schema::create('incident_followups', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('incident_report_id')->constrained()->cascadeOnDelete();
            $t->text('action');
            $t->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->date('due_date')->nullable();
            $t->datetime('completed_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_followups');
        Schema::dropIfExists('incident_reports');
    }
};
