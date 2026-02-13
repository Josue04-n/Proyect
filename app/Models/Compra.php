<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'numero_comprobante',
        'fecha_compra',
        'total',
        'estado',
        'observacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
    ];

    // Relación con Proveedor
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class); // Asegúrate de tener el modelo Proveedor
    }

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

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }
}