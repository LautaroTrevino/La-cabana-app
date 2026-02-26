<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'day_number'];

    public function ingredients()
    {
        // Solicitamos las 3 columnas en la relación
        return $this->belongsToMany(Ingredient::class, 'ingredient_menu')
                    ->withPivot('qty_jardin', 'qty_primaria', 'qty_secundaria', 'measure_unit')
                    ->withTimestamps();
    }
}