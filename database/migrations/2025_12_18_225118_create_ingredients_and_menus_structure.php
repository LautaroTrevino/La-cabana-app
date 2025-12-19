<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabla de Ingredientes (Agregamos DESCRIPTION y STOCK)
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable(); // <--- COLUMNA RECUPERADA
            $table->string('unit_type')->nullable(); 
            $table->decimal('stock', 10, 2)->default(0); 
            $table->timestamps();
        });

        // 2. Tabla de Menús (Con DAY_NUMBER para el orden)
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable(); 
            $table->integer('day_number'); // Necesario para el orderBy en el controlador
            $table->timestamps();
        });

        // 3. Tabla Intermedia (Recetas)
        Schema::create('ingredient_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('qty_jardin', 10, 4)->default(0);    
            $table->decimal('qty_primaria', 10, 4)->default(0);  
            $table->decimal('qty_secundaria', 10, 4)->default(0); 
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