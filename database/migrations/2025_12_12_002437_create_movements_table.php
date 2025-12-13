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
    Schema::create('movements', function (Blueprint $table) {
        $table->id();
        
        // Relación: El movimiento pertenece a un producto
        $table->foreignId('product_id')->constrained()->onDelete('cascade');
        
        // Tipo de movimiento: entrada (compra/devolución) o salida (venta/pérdida)
        $table->enum('type', ['entry', 'exit']); 
        
        // Cantidad que se mueve (siempre positivo aquí, la lógica la haremos luego)
        $table->integer('quantity'); 
        
        // Fecha y hora (created_at nos sirve para saber cuándo pasó)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
