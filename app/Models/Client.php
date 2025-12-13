<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Agregamos 'address' a la lista de campos permitidos
    protected $fillable = [
        'name', 
        'address', // <--- NUEVO CAMPO
        'cuit',    // Lo dejamos por si acaso, aunque no lo mostremos
        'quota_dmc', 
        'quota_dmc_alt', 
        'quota_comedor', 
        'quota_comedor_alt', 
        'quota_listo', 
        'quota_maternal'
    ];
}