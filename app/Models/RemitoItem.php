<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemitoItem extends Model
{
    use HasFactory;

    // Estos son los campos que guardamos desde el controlador
    protected $fillable = ['remito_id', 'name', 'quantity', 'unit', 'observation'];
    
    // Relación inversa (opcional, pero recomendada)
    public function remito()
    {
        return $this->belongsTo(Remito::class);
    }
}