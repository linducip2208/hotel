<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('efaktur_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('nomor_faktur')->nullable()->unique();
            $table->string('kode_transaksi')->nullable();
            $table->string('kode_status')->nullable();
            $table->string('npwp_penjual')->nullable();
            $table->string('npwp_pembeli')->nullable();
            $table->decimal('dpp', 14, 2)->nullable();
            $table->decimal('ppn', 14, 2)->nullable();
            $table->string('status')->default('draft');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ref_tahun')->nullable();
            $table->string('ref_bulan')->nullable();
            $table->string('ref_jenis')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users');
            $table->string('cancel_reason')->nullable();
            $table->nullableMorphs('source');
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index('nomor_faktur');
            $table->index('invoice_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('efaktur_records');
    }
};
