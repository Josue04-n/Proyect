<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tarifa extends Model
{
    //
    protected $table = 'tarifas';
    protected $fillable = [
        'tipo_prenda_id',
        'precio_mano_obra',
        'vigencia_desde',
        'vigencia_hasta',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'vigencia_desde' => 'date',
        'vigencia_hasta' => 'date',
        'precio_mano_obra' => 'decimal:2',
    ];

    // --- RELACIÓN CON LA PRENDA ---
    public function tipoPrenda(): BelongsTo
    {
        return $this->belongsTo(TipoPrenda::class);
    }

    // --- AUDITORÍA ---
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
        static::creating(function ($model) {
            $model->created_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }
}
