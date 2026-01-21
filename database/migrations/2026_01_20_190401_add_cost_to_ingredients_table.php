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
    Schema::table('ingredients', function (Blueprint $table) {
        // Costo por la unidad de medida que tengas (ej: precio por gramo o por unidad)
        $table->decimal('cost', 12, 4)->default(0)->after('unit_type');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            //
        });
    }
};
