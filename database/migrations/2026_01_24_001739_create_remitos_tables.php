<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabla de Cabecera de Remitos
        Schema::create('remitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('number')->unique(); // Ej: REM-123456
            $table->string('status')->default('Generado');
            $table->text('observation')->nullable();
            $table->timestamps();
        });

        // 2. Tabla de Detalles (Ítems del Remito)
        Schema::create('remito_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remito_id')->constrained('remitos')->onDelete('cascade');
            $table->string('name');      // Nombre del ingrediente/producto
            $table->decimal('quantity', 10, 3); // Cantidad (permite decimales para kg)
            $table->string('unit')->nullable(); // kg, gr, unidades
            $table->string('observation')->nullable(); // Para saber de qué menú vino
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('remito_items');
        Schema::dropIfExists('remitos');
    }
};