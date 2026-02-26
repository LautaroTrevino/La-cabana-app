<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Ingredientes
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->nullable(); // Unidad base (kg, lt)
            $table->decimal('cost', 10, 2)->default(0); 
            $table->decimal('stock', 10, 2)->default(0); 
            $table->timestamps();
        });

        // 2. Menús
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable(); // Comedor, DMC, etc.
            $table->integer('day_number')->nullable();
            $table->timestamps();
        });

        // 3. Receta (Tabla Intermedia con las 3 cantidades)
        Schema::create('ingredient_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            
            // --- AQUI RESTAURAMOS TUS 3 COLUMNAS ---
            $table->decimal('qty_jardin', 10, 4)->default(0);
            $table->decimal('qty_primaria', 10, 4)->default(0);
            $table->decimal('qty_secundaria', 10, 4)->default(0);
            
            // Unidad específica para esta receta (opcional, por si la receta usa gramos pero el stock es kg)
            $table->string('measure_unit')->default('grams'); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_menu');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('ingredients');
    }
};