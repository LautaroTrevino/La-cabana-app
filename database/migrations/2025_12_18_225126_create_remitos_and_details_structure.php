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
    Schema::create('remitos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
        $table->string('number'); 
        $table->date('date');
        $table->string('tipo')->default('remito'); // Aquí diferenciamos 'remito' de 'entrega'
        $table->text('observation')->nullable();
        $table->timestamps();
    });

    Schema::create('remito_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('remito_id')->constrained('remitos')->onDelete('cascade');
        
        // Puede ser un producto O un ingrediente
        $table->foreignId('product_id')->nullable()->constrained('products'); 
        $table->foreignId('ingredient_id')->nullable()->constrained('ingredients');
        
        $table->integer('quantity');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remitos_and_details_structure');
    }
};
