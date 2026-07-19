<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('property_licenses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('license_name');
            $t->string('license_number')->nullable();
            $t->string('issuing_authority')->nullable();
            $t->date('issue_date')->nullable();
            $t->date('expiry_date')->nullable();
            $t->unsignedSmallInteger('renewal_reminder_days')->default(30);
            $t->string('document_path')->nullable();
            $t->string('status')->default('active'); // active|expiring_soon|expired
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_licenses');
    }
};
