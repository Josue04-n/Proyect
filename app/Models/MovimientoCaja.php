<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';

    protected $fillable = [
        'fecha', 'tipo', 'monto', 'origen_id', 'origen_tipo', 'created_by'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function origen(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'origen_tipo', 'origen_id');
    }
    
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
        
    
}