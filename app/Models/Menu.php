<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'day_number'];

    // Relación con Ingredientes (NO con productos de stock)
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_menu')
                    ->withPivot('qty_jardin', 'qty_primaria', 'qty_secundaria')
                    ->withTimestamps();
    }
}