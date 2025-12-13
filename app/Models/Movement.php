<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'type', 'quantity', 'client_id']; // Debe estar 'client_id' aquí

    // Relación con el Producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    // Relación con el Cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}