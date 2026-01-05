<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabla de Ingredientes (Base de datos general)
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Unidad sugerida por defecto del ingrediente (ej: Harina -> Kg)
            $table->string('unit_type')->nullable(); 
            
            $table->decimal('stock', 10, 2)->default(0); 
            $table->timestamps();
        });

        // 2. Tabla de Menús (Nombres de platos y días)
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable(); 
            $table->integer('day_number'); 
            $table->timestamps();
        });

        // 3. Tabla Intermedia (Detalle de la Receta)
        Schema::create('ingredient_menu', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            
            // Cantidades por nivel educativo
            $table->decimal('qty_jardin', 10, 4)->default(0);    
            $table->decimal('qty_primaria', 10, 4)->default(0);  
            $table->decimal('qty_secundaria', 10, 4)->default(0); 
            
            // --- COLUMNA NUEVA CRÍTICA ---
            // Aquí guardamos si en ESTA receta usamos Gramos, CC o Unidades
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