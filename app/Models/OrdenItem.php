<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'orden_produccion_id',
        'tipo_prenda_id',
        'talla',
        'color',
        'cantidad',
    ];

    // Relación con el Padre
    public function ordenProduccion(): BelongsTo
    {
        return $this->belongsTo(OrdenProduccion::class);
    }

    // Relación con el Tipo de Prenda
    public function tipoPrenda(): BelongsTo
    {
        return $this->belongsTo(TipoPrenda::class);
    }
}