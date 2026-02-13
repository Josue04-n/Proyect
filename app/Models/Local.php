<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Local extends Model
{
    use HasFactory;

    protected $table = 'locales'; 

    protected $fillable = [
        'nombre',
        'direccion',
        'pasaje',
        'telefono',
        'hora_apertura',
        'hora_cierre',
        'es_principal',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'is_active' => 'boolean',
        // 'hora_apertura' y 'hora_cierre' Laravel los maneja bien como string H:i:s
    ];

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