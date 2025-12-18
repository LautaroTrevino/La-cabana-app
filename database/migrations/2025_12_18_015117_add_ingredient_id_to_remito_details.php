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
    Schema::table('remito_details', function (Blueprint $table) {
        // Hacemos nullable product_id porque ahora puede ser un ingrediente
        $table->unsignedBigInteger('product_id')->nullable()->change();
        
        // Agregamos el ingrediente
        $table->foreignId('ingredient_id')->nullable()->constrained()->onDelete('cascade')->after('product_id');
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remito_details', function (Blueprint $table) {
            //
        });
    }
};
