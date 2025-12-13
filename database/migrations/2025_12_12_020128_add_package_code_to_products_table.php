<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('products', function (Blueprint $table) {
        // Agregamos el código de bulto, debe ser único, pero puede quedar vacío (nullable)
        // Lo ponemos después del código normal para mantener orden
        $table->string('package_code')->nullable()->unique()->after('code');
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('package_code');
    });
}
};
