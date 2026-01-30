<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Operario;

class Cuenta extends Model
{
    //
    protected $table = 'cuentas';
    protected $fillable = [
        'num_cuenta',
        'operario_id',
        'saldo',
        'created_by',
        'updated_by',
    ];

    public function operario():BelongsTo
    {
        return $this->belongsTo(Operario::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

        protected static function booted(): void{
        
        static::creating(function ($model){
            $model->created_by = auth()->id();
        });
        
        static::updating(function ($model){
            $model->updated_by = auth()->id();
        });
    }

    
}
