<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoOperario extends Model
{
    use HasFactory;

    protected $table = 'pagos_operarios';

    protected $fillable = [
        'operario_id',
        'usuario_paga_id',
        'fecha_pago',
        'monto',
        'forma_pago',
        'observacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
    ];

    // --- RELACIONES ---
    public function operario(): BelongsTo
    {
        return $this->belongsTo(Operario::class);
    }

    public function usuarioPaga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_paga_id');
    }

    // --- AUDITORÍA AUTOMÁTICA ---
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $user = auth()->id();
            $model->created_by = $user;
            // Si no se especificó quién paga, asumimos que es el usuario logueado
            if (empty($model->usuario_paga_id)) {
                $model->usuario_paga_id = $user;
            }
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }
}