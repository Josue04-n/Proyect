<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrendaTienda extends Model
{
    use HasFactory;

    protected $table = 'prendas_tienda';

    protected $fillable = [
        'local_id',
        'tipo_prenda_id',
        'talla',
        'color',
        'precio_venta',
        'stock_actual',
        'created_by',
        'updated_by',
    ];

    // --- RELACIONES ---
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class);
    }

    public function tipoPrenda(): BelongsTo
    {
        return $this->belongsTo(TipoPrenda::class);
    }

    // --- AUDITORÍA ---
    public function createdBy(): BelongsTo 
    { return $this->belongsTo(User::class, 'created_by'); }

    public function updatedBy(): BelongsTo 
    { return $this->belongsTo(User::class, 'updated_by'); }

    protected static function booted(): void
    {
        static::creating(function ($model) { $model->created_by = auth()->id(); });
        static::updating(function ($model) { $model->updated_by = auth()->id(); });
    }
}