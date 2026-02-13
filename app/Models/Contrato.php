<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contrato extends Model
{
    //
    protected $table = 'contratos';
    protected $fillable = [
        'cliente_id',
        'condigo_contrato',
        'descripcion',
        'fecha_inicio',
        'fecha_fin_estimada',
        'presupuesto_total',
        'estado',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    //Castear la Fecha
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin_estimada' => 'date',
    ];

    //Relacion con Cliente
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    //Auditoria de Usuario
    protected static function booted(): void{
        
        static::creating(function ($model){
            $model->created_by = auth()->id();
            if (empty($model->codigo_contrato)) {
                $model->codigo_contrato = 'CTR-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            }
        });
        
        static::updating(function ($model){
            $model->updated_by = auth()->id();
        });
    }
}