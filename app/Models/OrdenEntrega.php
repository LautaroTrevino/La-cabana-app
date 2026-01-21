<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenEntrega extends Model
{
    protected $fillable = ['client_id', 'date', 'number', 'menu_type', 'observation'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function details()
    {
        return $this->hasMany(OrdenEntregaDetail::class);
    }
}