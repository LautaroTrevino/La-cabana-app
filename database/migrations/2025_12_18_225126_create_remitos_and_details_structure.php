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
        // 1. TABLA REMITOS (Encabezado)
        Schema::create('remitos', function (Blueprint $table) {
            $table->id();
            
            // Relación con Cliente
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            
            $table->string('number'); 
            $table->date('date');
            
            // Diferenciamos si es una entrega real de stock o un remito de menú
            $table->string('tipo')->default('remito'); 
            
            // Agregado para compatibilidad con el controlador
            $table->string('status')->default('active'); 
            
            $table->text('observation')->nullable();
            $table->timestamps();
        });

        // 2. TABLA DETALLES (Renglones del remito)
        Schema::create('remito_details', function (Blueprint $table) {
            $table->id();
            
            // Relación con el encabezado
            $table->foreignId('remito_id')->constrained('remitos')->onDelete('cascade');
            
            // Puede ser un producto (stock) O un ingrediente (menú)
            // Agregué onDelete cascade para que no queden datos huérfanos si borras un producto
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade'); 
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->onDelete('cascade');
            
            // CORRECCIÓN CRÍTICA:
            // Usamos decimal para permitir 0.500 Kg (500 gramos).
            // (12 dígitos en total, 4 decimales de precisión)
            $table->decimal('quantity', 12, 4);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // El orden importa: Primero borramos la tabla "hija" (detalles)
        Schema::dropIfExists('remito_details');
        // Luego la tabla "padre" (remitos)
        Schema::dropIfExists('remitos');
    }
};