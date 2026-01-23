<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Verificamos si la columna existe antes de crearla para evitar errores
            if (!Schema::hasColumn('clients', 'cupo_jardin')) {
                $table->integer('cupo_jardin')->default(0)->after('address');
            }
            if (!Schema::hasColumn('clients', 'cupo_primaria')) {
                $table->integer('cupo_primaria')->default(0)->after('cupo_jardin');
            }
            if (!Schema::hasColumn('clients', 'cupo_secundaria')) {
                $table->integer('cupo_secundaria')->default(0)->after('cupo_primaria');
            }
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['cupo_jardin', 'cupo_primaria', 'cupo_secundaria']);
        });
    }
};