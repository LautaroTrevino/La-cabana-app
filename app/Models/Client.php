<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        // 1. Datos de Contacto e Identificación
        'name', 
        'address',
        'phone',
        'email',
        'cuit',
        'level',   

        // 2. CUPOS OPERATIVOS (Por Tipo de Servicio)
        // Estos son los que definen cuánta comida se prepara y cobra
        'quota_comedor',      // Comedor Estándar
        'quota_dmc',          // Desayuno/Merienda
        'quota_comedor_alt',  // Comedor Alternativo
        'quota_dmc_alt',      // DMC Alternativo
        'quota_lcb',          // Listo Consumo (Antes quota_listo)
        'quota_maternal',

        // 3. VALORES / PRECIOS (Opcional)
        // Si esta escuela tiene un precio distinto al general
        'valor_dmc', 
        'valor_comedor', 
        'valor_lc',
    ];

    /**
     * Relación con los remitos
     */
    public function remitos()
    {
        return $this->hasMany(Remito::class);
    }

    /**
     * Relación con las órdenes de entrega (Salida de Depósito)
     */
    public function ordenes()
    {
        return $this->hasMany(OrdenEntrega::class);
    }
}