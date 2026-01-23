<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Agregamos el cupo maternal si no existe
            if (!Schema::hasColumn('clients', 'quota_maternal')) {
                $table->integer('quota_maternal')->default(0)->after('quota_lcb');
            }
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('quota_maternal');
        });
    }
};