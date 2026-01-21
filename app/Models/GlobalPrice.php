<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalPrice extends Model
{
    protected $fillable = ['valor_dmc', 'valor_comedor', 'valor_lc'];
}