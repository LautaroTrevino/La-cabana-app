<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'date', 'number', 'observation'];

    // Relación: Un remito pertenece a un cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación: Un remito tiene muchos detalles (productos)
    public function details()
    {
        return $this->hasMany(RemitoDetail::class);
    }
}