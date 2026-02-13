<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaProduccion extends Model
{
    use HasFactory;

    protected $table = 'entregas_produccion';

    protected $fillable = [
        'asignacion_trabajo_id',
        'cantidad_entregada',
        'fecha_recibo_real',
        'tarifa_aplicada',
        'monto_generado',
        'observacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_recibo_real' => 'datetime',
        'tarifa_aplicada' => 'decimal:2',
        'monto_generado' => 'decimal:2',
    ];

    // Relación con la Asignación (Para saber de qué orden viene)
    public function asignacionTrabajo(): BelongsTo
    {
        return $this->belongsTo(AsignacionTrabajo::class, 'asignacion_trabajo_id');
    }

    // Auditoría
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }

    protected static function booted(): void
    {
        static::creating(function ($model) { $model->created_by = auth()->id(); });
        static::updating(function ($model) { $model->updated_by = auth()->id(); });
    }
}