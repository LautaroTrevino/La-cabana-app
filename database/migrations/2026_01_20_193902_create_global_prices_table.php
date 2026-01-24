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
        Schema::create('global_prices', function (Blueprint $table) {
            $table->id();
            
            // PRECIOS COMEDOR
            $table->decimal('valor_comedor', 10, 2)->default(0);
            $table->decimal('valor_comedor_alt', 10, 2)->default(0); // Faltaba este

            // PRECIOS DMC
            $table->decimal('valor_dmc', 10, 2)->default(0);
            $table->decimal('valor_dmc_alt', 10, 2)->default(0); // Faltaba este

            // OTROS SERVICIOS
            $table->decimal('valor_lc', 10, 2)->default(0);
            $table->decimal('valor_maternal', 10, 2)->default(0); // Faltaba este

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_prices');
    }
};