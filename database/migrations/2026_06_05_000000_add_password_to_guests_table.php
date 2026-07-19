<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $t) {
            $t->string('password')->nullable()->after('email');
            $t->rememberToken()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $t) {
            $t->dropColumn(['password', 'remember_token']);
        });
    }
};
