<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Operario;
use App\Models\OrdenItem;

class AsignacionTrabajo extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_trabajo';

    protected $fillable = [
        'orden_item_id',
        'operario_id',
        'cantidad_asignada',
        'fecha_asignacion',
        'fecha_estimada_entrega',
        'estado',
        'observacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'fecha_estimada_entrega' => 'date',
    ];

    // --- RELACIONES ---

    // 1. El Item específico (Camisa Talla M)
    public function ordenItem(): BelongsTo
    {
        return $this->belongsTo(OrdenItem::class, 'orden_item_id');
    }

    // 2. El Operario
    public function operario(): BelongsTo
    {
        return $this->belongsTo(Operario::class);
    }

    // 3. Auditoría
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }

    protected static function booted(): void
    {
        static::creating(function ($model) { $model->created_by = auth()->id(); });
        static::updating(function ($model) { $model->updated_by = auth()->id(); });
    }
}