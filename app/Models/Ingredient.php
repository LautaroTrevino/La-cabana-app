<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    // AQUI ESTABA EL ERROR: Cambiamos 'unit_type' por 'unit'
    protected $fillable = ['name', 'description', 'unit', 'cost', 'stock'];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'ingredient_menu')
                    ->withPivot('quantity', 'unit')
                    ->withTimestamps();
    }
}