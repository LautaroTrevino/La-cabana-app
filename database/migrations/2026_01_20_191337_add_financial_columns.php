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
    // 1. Precios por Cliente
    Schema::table('clients', function (Blueprint $table) {
        $table->decimal('valor_dmc', 10, 2)->default(0);      // Para DMC, DMC Alt, Maternal
        $table->decimal('valor_comedor', 10, 2)->default(0);  // Para Comedor, Comedor Alt
        $table->decimal('valor_lc', 10, 2)->default(0);       // Para Listo Consumo
    });

    // 2. Categoría en el Remito (para saber qué precio cobrar ese día)
    Schema::table('remitos', function (Blueprint $table) {
        $table->string('menu_type')->nullable(); // Guardará 'DMC', 'Comedor', etc.
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
