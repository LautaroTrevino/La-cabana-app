<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('clients', function (Blueprint $table) {
        $table->id();
        
        // Datos de la Escuela/Cliente
        $table->string('name');
        $table->string('cuit')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->text('address')->nullable();
        
        // Datos de Cupos
        $table->integer('quota_dmc')->default(0);
        $table->integer('quota_dmc_alt')->default(0);
        $table->integer('quota_comedor')->default(0);
        $table->integer('quota_comedor_alt')->default(0);
        $table->integer('quota_listo')->default(0);
        $table->integer('quota_maternal')->default(0);

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('clients');
}
};
