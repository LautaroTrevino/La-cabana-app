<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // Código / SKU
        $table->string('name');           // Nombre
        $table->text('description')->nullable(); // Descripción
        $table->string('brand')->nullable();     // Marca
        $table->string('presentation')->nullable(); // Presentación (Ej: Botella, Caja)
        
        $table->integer('units_per_package')->default(1); // Unidades por bulto
        $table->decimal('price_per_unit', 10, 2)->default(0);    // Precio unitario
        $table->decimal('price_per_package', 10, 2)->default(0); // Precio bulto
        
        $table->integer('stock')->default(0); // Stock total (en unidades)
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};