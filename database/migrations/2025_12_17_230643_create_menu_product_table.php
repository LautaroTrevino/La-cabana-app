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
    Schema::create('menu_product', function (Blueprint $table) {
        $table->id();
        $table->foreignId('menu_id')->constrained()->onDelete('cascade');
        $table->foreignId('product_id')->constrained()->onDelete('cascade');
        
        // Estas son las 3 columnas de tu Excel
        $table->decimal('qty_jardin', 10, 4)->default(0);    
        $table->decimal('qty_primaria', 10, 4)->default(0);  
        $table->decimal('qty_secundaria', 10, 4)->default(0); 
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_product');
    }
};
