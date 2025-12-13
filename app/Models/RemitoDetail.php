<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemitoDetail extends Model
{
    use HasFactory;

    // ESTA LÍNEA ES OBLIGATORIA PARA GUARDAR DATOS
    protected $fillable = ['remito_id', 'product_id', 'quantity'];

    // Relación inversa: Un detalle pertenece a un Producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}