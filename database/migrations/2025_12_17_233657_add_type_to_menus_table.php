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
    Schema::table('menus', function (Blueprint $table) {
        // Agregamos el tipo de menú y el día del ciclo (1 al 10)
        $table->string('type')->after('name'); // Comedor, DMC, etc.
        $table->integer('day_number')->after('type'); // 1, 2, 3...
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            //
        });
    }
};
