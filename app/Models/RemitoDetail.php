<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemitoDetail extends Model
{
    use HasFactory;

    // Campos permitidos para carga masiva
    protected $fillable = [
        'remito_id', 
        'product_id', 
        'ingredient_id', 
        'quantity'
    ];

    /**
     * Relación con el Producto (Carga manual desde Depósito/Administración)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación con el Ingrediente (Carga automática desde Menú)
     */
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
    
    /**
     * Relación con ArticuloMenu (Opcional, si aún usas este modelo)
     */
    public function articuloMenu()
    {
        return $this->belongsTo(ArticuloMenu::class, 'articulo_menu_id');
    }
}