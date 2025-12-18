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
    // 1. Tabla de Ingredientes (Genéricos para recetas)
    Schema::create('ingredients', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Ej: "Polenta"
        $table->text('description')->nullable(); // Ej: "Harina de maíz precocida"
        $table->timestamps();
    });

    // 2. Tabla Intermedia (Vincula Menú con Ingredientes y Cantidades)
    Schema::create('ingredient_menu', function (Blueprint $table) {
        $table->id();
        $table->foreignId('menu_id')->constrained()->onDelete('cascade');
        $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
        
        // Las 3 columnas de cantidades por nivel
        $table->decimal('qty_jardin', 10, 4)->default(0);    
        $table->decimal('qty_primaria', 10, 4)->default(0);  
        $table->decimal('qty_secundaria', 10, 4)->default(0); 
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
