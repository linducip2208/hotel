<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $t) {
            $t->string('previous_hash', 64)->nullable()->after('metadata');
            $t->string('entry_hash', 64)->nullable()->after('previous_hash');
            $t->index('entry_hash');
        });

        // Daily checksum table — exported to external storage (S3 versioned)
        Schema::create('audit_log_checkpoints', function (Blueprint $t) {
            $t->id();
            $t->date('checkpoint_date')->unique();
            $t->unsignedBigInteger('first_entry_id');
            $t->unsignedBigInteger('last_entry_id');
            $t->unsignedInteger('entries_count');
            $t->string('cumulative_hash', 64);
            $t->timestamp('exported_at')->nullable();
            $t->string('export_destination')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log_checkpoints');
        Schema::table('audit_logs', function (Blueprint $t) {
            $t->dropIndex(['entry_hash']);
            $t->dropColumn(['previous_hash', 'entry_hash']);
        });
    }
};
