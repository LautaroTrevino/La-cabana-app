<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
    Schema::table('clients', function (Blueprint $table) {
        // Agregamos las columnas que faltan
        $table->integer('cupo_jardin')->default(0)->after('address');
        $table->integer('cupo_primaria')->default(0)->after('cupo_jardin');
        $table->integer('cupo_secundaria')->default(0)->after('cupo_primaria');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    Schema::table('clients', function (Blueprint $table) {
        $table->dropColumn(['cupo_jardin', 'cupo_primaria', 'cupo_secundaria']);
    });
    }
};
