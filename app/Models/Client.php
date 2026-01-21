<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        // Datos de Contacto e Identificación
        'name', 
        'address',
        'phone',
        'email',
        'cuit',
        'level',   

        // Cupos por Nivel (Usados para cálculo de cantidades en recetas)
        'cupo_jardin', 
        'cupo_primaria', 
        'cupo_secundaria',

        // Valores Financieros (NUEVO: Para el Balance)
        'valor_dmc', 
        'valor_comedor', 
        'valor_lc',

        // Cupos Específicos (Legado/Otros)
        'quota_dmc', 
        'quota_dmc_alt', 
        'quota_comedor', 
        'quota_comedor_alt', 
        'quota_listo', 
        'quota_maternal',
    ];

    /**
     * Relación con los remitos
     */
    public function remitos()
    {
        return $this->hasMany(Remito::class);
    }
}