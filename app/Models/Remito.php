<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'date', 'number', 'status', 'observation'];

    // Relación con la Escuela
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ESTA ES LA FUNCIÓN QUE FALTABA Y CAUSABA EL ERROR
    public function items()
    {
        return $this->hasMany(RemitoItem::class);
    }
}