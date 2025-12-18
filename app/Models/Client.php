<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Agregamos 'level' para identificar si es jardin, primaria o secundaria
    protected $fillable = [
        'name', 
        'address',
        'level',   // <--- NUEVO: Define qué columna del menú usar
        'cuit',
        'quota_dmc', 
        'quota_dmc_alt', 
        'quota_comedor', 
        'quota_comedor_alt', 
        'quota_listo', 
        'quota_maternal'
    ];

    /**
     * Relación con los remitos
     */
    public function remitos()
    {
        return $this->hasMany(Remito::class);
    }
}