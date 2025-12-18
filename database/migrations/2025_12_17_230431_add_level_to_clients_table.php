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
        // Esta es la línea del Paso 3: Agrega la columna 'level'
        // Si ya existe, puedes borrar esta línea o el archivo, pero si está vacío, pega esto:
        if (!Schema::hasColumn('clients', 'level')) {
            $table->enum('level', ['jardin', 'primaria', 'secundaria'])
                  ->default('primaria')
                  ->after('address'); // Para que quede ordenada después de la dirección
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
};
