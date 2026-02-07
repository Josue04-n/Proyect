<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

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

    // --- RELACIONES ---
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

    protected static function booted(): void
    {
        static::creating(function ($transferencia) {
            $itemOrigen = PrendaTienda::find($transferencia->prenda_tienda_id);
            
            if (!$itemOrigen || $itemOrigen->stock_actual < $transferencia->cantidad) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente en origen. Disponible: " . ($itemOrigen->stock_actual ?? 0)
                ]);
            }
            
            $transferencia->created_by = auth()->id();
        });

        static::created(function ($transferencia) {
            $itemOrigen = $transferencia->prendaTienda;
            $itemOrigen->decrement('stock_actual', $transferencia->cantidad);

            $itemDestino = PrendaTienda::firstOrCreate(
                [
                    'local_id' => $transferencia->destino_local_id,
                    'tipo_prenda_id' => $itemOrigen->tipo_prenda_id,
                    'talla' => $itemOrigen->talla,
                    'color' => $itemOrigen->color,
                ],
                [
                    'precio_venta' => $itemOrigen->precio_venta,
                    'stock_actual' => 0,
                    'created_by' => auth()->id(),
                ]
            );

            $itemDestino->increment('stock_actual', $transferencia->cantidad);
        });
    }
}