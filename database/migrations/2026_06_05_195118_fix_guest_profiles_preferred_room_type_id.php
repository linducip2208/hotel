<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_profiles', function (Blueprint $t) {
            $t->dropColumn('preferred_room_type_id');
        });

        Schema::table('guest_profiles', function (Blueprint $t) {
            $t->foreignId('preferred_room_type_id')
                ->nullable()
                ->after('avg_ancillary_spend')
                ->constrained('room_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guest_profiles', function (Blueprint $t) {
            $t->dropForeign(['preferred_room_type_id']);
            $t->dropColumn('preferred_room_type_id');
        });

        Schema::table('guest_profiles', function (Blueprint $t) {
            $t->string('preferred_room_type_id')
                ->nullable()
                ->after('avg_ancillary_spend');
        });
    }
};
