<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemitoItem extends Model
{
    use HasFactory;

    protected $fillable = ['remito_id', 'name', 'quantity', 'unit', 'observation'];

    public function remito()
    {
        return $this->belongsTo(Remito::class);
    }

    /**
     * Devuelve la cantidad convertida y la unidad legible.
     * grams → kg si >= 1000, sino g
     * cc    → L  si >= 1000, sino ml
     * un.   → siempre unidades
     */
    public function getFormattedQuantityAttribute(): string
    {
        $qty  = (float) $this->quantity;
        $unit = $this->unit;

        if ($unit === 'g.') {
            if ($qty >= 1000) {
                return number_format($qty / 1000, 3, ',', '.') . ' kg';
            }
            return number_format($qty, 0, ',', '.') . ' g';
        }

        if ($unit === 'cc.') {
            if ($qty >= 1000) {
                return number_format($qty / 1000, 3, ',', '.') . ' L';
            }
            return number_format($qty, 0, ',', '.') . ' ml';
        }

        // Unidades u otros
        return number_format($qty, 0, ',', '.') . ' un.';
    }
}
