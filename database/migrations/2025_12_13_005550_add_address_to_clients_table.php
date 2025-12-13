<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Solo agregamos la columna si NO existe
        if (!Schema::hasColumn('clients', 'address')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('address')->nullable()->after('name');
            });
        }
    }

    public function down()
    {
        // Solo borramos la columna si SI existe
        if (Schema::hasColumn('clients', 'address')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
};