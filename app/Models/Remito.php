<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'date', 'number', 'status', 'observation'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ESTA FUNCIÓN ES LA QUE SOLUCIONA EL ERROR
    public function items()
    {
        return $this->hasMany(RemitoItem::class);
    }
}