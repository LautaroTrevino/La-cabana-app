<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // 1. Borramos las columnas incorrectas (si existen)
            $columnasBorrar = ['cupo_jardin', 'cupo_primaria', 'cupo_secundaria'];
            foreach ($columnasBorrar as $col) {
                if (Schema::hasColumn('clients', $col)) {
                    $table->dropColumn($col);
                }
            }

            // 2. Agregamos las columnas CORRECTAS por Servicio
            // Usamos 'quota_' para mantener el estándar en inglés/código
            if (!Schema::hasColumn('clients', 'quota_comedor')) 
                $table->integer('quota_comedor')->default(0)->after('address');
            
            if (!Schema::hasColumn('clients', 'quota_dmc')) 
                $table->integer('quota_dmc')->default(0)->after('quota_comedor');

            if (!Schema::hasColumn('clients', 'quota_comedor_alt')) 
                $table->integer('quota_comedor_alt')->default(0)->after('quota_dmc');

            if (!Schema::hasColumn('clients', 'quota_dmc_alt')) 
                $table->integer('quota_dmc_alt')->default(0)->after('quota_comedor_alt');

            if (!Schema::hasColumn('clients', 'quota_lcb')) 
                $table->integer('quota_lcb')->default(0)->comment('Listo Consumo')->after('quota_dmc_alt');
        });
    }

    public function down()
    {
        // En el reverso, borramos las nuevas y (opcionalmente) volveríamos a las viejas
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['quota_comedor', 'quota_dmc', 'quota_comedor_alt', 'quota_dmc_alt', 'quota_lcb']);
        });
    }
};