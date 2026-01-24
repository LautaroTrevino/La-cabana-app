<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalPrice extends Model
{
    use HasFactory;

    // Estos son los campos que permitimos editar
    protected $fillable = [
        'valor_comedor', 
        'valor_comedor_alt',
        'valor_dmc', 
        'valor_dmc_alt',
        'valor_lc', 
        'valor_maternal'
    ];
}