<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'fecha_emision',
        'subtotal',
        'descuento',
        'impuestos',
        'total',
        'estado_pago',
        'metodo_pago',
        'requiere_factura', // <--- NUEVO
        'clave_acceso_sri', // <--- NUEVO
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'requiere_factura' => 'boolean', // <--- Importante para el Toggle
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // --- RELACIONES ---

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación con los detalles (Items de la venta)
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --- AUDITORÍA AUTOMÁTICA ---
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_by = auth()->id();
        });
        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }
}