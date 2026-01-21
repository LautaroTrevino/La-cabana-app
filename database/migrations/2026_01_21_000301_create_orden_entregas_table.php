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
    Schema::create('orden_entregas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained()->onDelete('cascade');
        $table->date('date');
        $table->string('number')->unique(); // N° de Orden Interna
        
        // VITAL: Para saber qué precio cobrar en el Balance (Comedor, DMC, etc)
        $table->string('menu_type')->nullable(); 
        
        $table->text('observation')->nullable();
        $table->timestamps();
    });

    // Detalle de los productos que salieron del stock
    Schema::create('orden_entrega_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('orden_entrega_id')->constrained()->onDelete('cascade');
        
        // Relación directa con PRODUCTOS (porque sale del depósito)
        $table->foreignId('product_id')->constrained();
        
        $table->decimal('quantity', 10, 4); // Cantidad que salió
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_entregas');
    }
};
