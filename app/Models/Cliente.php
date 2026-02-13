<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Cliente extends Model
{
    //
    protected $table = 'clientes';
    protected $fillable = [
        'is_active',
        'tipo_cliente',
        'identificacion',
        'razon_social',
        'primer_nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'direccion',
        'email',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function getNombreCompletoAttribute()
    {
        if ($this->tipo_cliente === 'juridica') {
            return $this->razon_social;
        }

        return trim("{$this->primer_nombre} {$this->segundo_nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    // --- RELACIONES DE AUDITORÍA ---
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Auto-llenado de auditoría
        protected static function booted(): void{
        
        static::creating(function ($model){
            $model->created_by = auth()->id();
        });
        
        static::updating(function ($model){
            $model->updated_by = auth()->id();
        });
    }
}
