<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'is_active',
        'razon_social',
        'ruc',
        'contacto',
        'telefono',
        'email',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // --- RELACIONES ---
    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
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
        static::creating(function ($model) 
        { $model->created_by = auth()->id(); });
        
        static::updating(function ($model) 
        { $model->updated_by = auth()->id(); });
    }
}