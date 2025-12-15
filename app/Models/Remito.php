<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    use HasFactory;

    // ACTUALIZADO: Agregamos 'tipo', 'observation' y estandarizamos nombres para coincidir con el Controlador
    protected $fillable = [
        'client_id',   // Clave foránea al cliente
        'number',      // Número de remito (o 'numero_remito' si prefieres, pero debe coincidir en BD)
        'date',        // Fecha
        'tipo',        // VITAL: Aquí guardaremos 'remito' o 'entrega'
        'observation', // Observaciones opcionales
        'status'       // 'estado' (opcional, si quieres manejar anulaciones después)
    ];

    // Relación 1: Un remito pertenece a un Cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación 2: Un remito tiene muchos detalles (productos)
    public function details()
    {
        return $this->hasMany(RemitoDetail::class);
    }
}