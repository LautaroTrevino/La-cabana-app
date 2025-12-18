<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    // Aquí permitimos guardar nombre y descripción
    protected $fillable = ['name', 'description','unit_type'];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'ingredient_menu')
                    ->withPivot('qty_jardin', 'qty_primaria', 'qty_secundaria')
                    ->withTimestamps();
    }
}