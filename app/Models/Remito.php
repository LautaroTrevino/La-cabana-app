<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    use HasFactory;

    // Campos que permitimos guardar
    protected $fillable = ['numero_remito', 'fecha', 'cliente', 'estado'];

    // Relación: Un remito tiene muchos detalles
    public function details()
    {
        return $this->hasMany(RemitoDetail::class);
    }
}