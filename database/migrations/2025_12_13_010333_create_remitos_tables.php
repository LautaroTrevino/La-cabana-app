<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabla Encabezado (REMITOS)
        Schema::create('remitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Fecha del remito
            $table->string('number')->nullable(); // Número de remito (opcional o autogenerado)
            $table->text('observation')->nullable();
            $table->timestamps();
        });

        // 2. Tabla Detalle (ITEMS DEL REMITO)
        Schema::create('remito_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remito_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
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