<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregamos la columna y la clave foránea
        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('client_id')
                  ->nullable() // Permitimos que sea nulo (para Entradas o movimientos sin cliente)
                  ->constrained() // Crea la clave foránea a la tabla 'clients'
                  ->onDelete('set null') // Si borras un cliente, el historial se mantiene (el client_id se pone en NULL)
                  ->after('type'); // Lo colocamos después de la columna 'type'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Borramos la clave foránea y la columna
        Schema::table('movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn('client_id');
        });
    }
};