<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla Principal: REMITOS (Sin dinero)
        Schema::create('remitos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_remito')->unique(); 
            $table->date('fecha');
            $table->string('cliente'); 
            // ELIMINAMOS LA COLUMNA TOTAL
            $table->string('estado')->default('pendiente'); 
            $table->timestamps();
        });

        // 2. Tabla Detalle: PRODUCTOS DEL REMITO
        Schema::create('remito_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remito_id')->constrained('remitos')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            
            // CAMBIO: Decimal para permitir "1.5 KG" o "200 Unidades"
            $table->decimal('quantity', 10, 2); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remito_details');
        Schema::dropIfExists('remitos');
    }
};