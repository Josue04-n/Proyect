<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaInventario extends Model
{
    use HasFactory;

    protected $table = 'transferencias_inventario';

    protected $fillable = [
        'origen_local_id',
        'destino_local_id',
        'prenda_tienda_id',
        'cantidad',
        'fecha_transferencia',
        'observacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_transferencia' => 'datetime',
    ];

    public function origenLocal(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'origen_local_id');
    }

    public function destinoLocal(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'destino_local_id');
    }

    public function prendaTienda(): BelongsTo
    {
        return $this->belongsTo(PrendaTienda::class, 'prenda_tienda_id');
    }

    public function createdBy(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'created_by'); 
    }

    public function updatedBy(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'updated_by'); 
    }
}