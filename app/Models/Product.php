<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'package_code',
        'name', 
        'description', 
        'brand', 
        'presentation', 
        'units_per_package', 
        'price_per_unit', 
        'price_per_package', 
        'stock'
    ];

    /**
     * Relación con los movimientos de stock
     */
    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * Relación con los Menús (Ingredientes)
     * Permite saber en qué menús participa este producto y sus cantidades por nivel
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class)
                    ->withPivot('qty_jardin', 'qty_primaria', 'qty_secundaria')
                    ->withTimestamps();
    }
}