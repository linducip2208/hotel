<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $t) {
            if (Schema::hasColumn('tenants', 'db_name') && ! Schema::hasColumn('tenants', 'database_name')) {
                $t->renameColumn('db_name', 'database_name');
            }
        });

        Schema::table('tenants', function (Blueprint $t) {
            if (Schema::hasColumn('tenants', 'database_name')) {
                $t->string('database_name', 64)->nullable()->unique()->change();
            } else {
                $t->string('database_name', 64)->nullable()->unique()->after('db_host');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $t) {
            if (Schema::hasColumn('tenants', 'database_name')) {
                $t->dropUnique(['database_name']);
                $t->renameColumn('database_name', 'db_name');
            }
        });
    }
};
