<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenProduccion extends Model
{
    use HasFactory;

    protected $table = 'ordenes_produccion';

    // Ya no incluimos talla, color, etc. en el fillable del padre
    protected $fillable = [
        'cliente_id',
        'contrato_id',
        'fecha_recepcion',
        'fecha_entrega_estimada',
        'estado',
        'observacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'fecha_entrega_estimada' => 'date',
    ];

    // --- RELACIÓN NUEVA: Una orden tiene muchos items ---
    public function items(): HasMany
    {
        return $this->hasMany(OrdenItem::class);
    }

    // --- Relaciones existentes ---
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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