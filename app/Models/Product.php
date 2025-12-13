<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Estos son los campos que permitimos llenar desde el formulario
   protected $fillable = [
    'code', 
    'package_code', // <--- NUEVO
    'name', 
    'description', 
    'brand', 
    'presentation', 
    'units_per_package', 
    'price_per_unit', 
    'price_per_package', 
    'stock'
];

public function movements()
    {
        return $this->hasMany(Movement::class);
    }
}
