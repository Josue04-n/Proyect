<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operario extends Model
{
    //
    protected $table = 'operarios';
    protected $fillable = [
        'cedula',
        'primer_nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'direccion',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->primer_nombre} {$this->segundo_nombre} {$this->apellido_paterno} {$this->apellido_materno}");
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
