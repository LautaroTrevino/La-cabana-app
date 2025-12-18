<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    use HasFactory;

    // IMPORTANTE: Estos nombres deben coincidir EXACTAMENTE con tu migración
    protected $fillable = [
        'client_id', 
        'number', 
        'date', 
        'tipo', 
        'observation', 
        'status'
    ];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function details() {
        return $this->hasMany(RemitoDetail::class);
    }
}