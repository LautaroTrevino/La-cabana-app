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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            
            // 1. Relación con el producto (Obligatorio)
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // 2. Tipo de movimiento: entrada o salida
            $table->enum('type', ['entry', 'exit']); 
            
            // 3. Cantidad
            // IMPORTANTE: Lo cambié a 'decimal' por si algún día usas kilos o litros (ej: 1.5 kg).
            // Si estás 100% seguro de que solo usarás unidades enteras, puedes dejarlo como $table->integer('quantity');
            $table->decimal('quantity', 10, 2); 
            
            // --- COLUMNAS NUEVAS QUE FALTABAN ---
            
            // 4. Cliente / Escuela (Puede ser nulo, ej: en una carga de stock o rotura)
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            
            // 5. Observación (Para guardar "Baja por rotura", "Escáner Rápido", etc.)
            $table->string('observation')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};