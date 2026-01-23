<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenEntrega extends Model
{
    protected $fillable = ['client_id', 'date', 'number', 'menu_type', 'observation'];

    // Relación con la Escuela
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación con los productos de la lista
    public function details()
    {
        return $this->hasMany(OrdenEntregaDetail::class);
    }
}