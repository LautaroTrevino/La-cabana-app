<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabla Principal REMITOS
        Schema::create('remitos', function (Blueprint $table) {
            $table->id();
            
            // Relación con Clients (Asegúrate que tu tabla de clientes se llame 'clients')
            // Si tu tabla se llama 'clientes', cambia 'clients' por 'clientes' aquí abajo.
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            
            $table->string('number'); // Número (REM-1234 o ENT-1234)
            $table->date('date');     // Fecha
            
            // AQUÍ ESTÁ LA CLAVE: 'tipo' ya viene incluido desde el nacimiento de la tabla
            $table->string('tipo')->default('remito'); // 'remito' o 'entrega'
            
            $table->text('observation')->nullable();
            $table->string('status')->default('active'); 
            $table->timestamps();
        });

        // 2. Tabla Detalles REMITO_DETAILS
        Schema::create('remito_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remito_id')->constrained('remitos')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products'); // Asume tabla 'products'
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('remito_details');
        Schema::dropIfExists('remitos');
    }
};