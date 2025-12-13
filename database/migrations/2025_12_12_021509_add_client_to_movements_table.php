<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('movements', function (Blueprint $table) {
        // Agregamos la columna 'client' (nullable porque en las ENTRADAS quizás no hay cliente)
        $table->string('client')->nullable()->after('type');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            //
        });
    }
};
