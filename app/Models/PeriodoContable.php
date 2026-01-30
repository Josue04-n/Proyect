<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoContable extends Model
{
    //Protecion la tabla
    protected $table = 'periodos_contables';
    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'created_at',
        'updated_at',
    ];

    //Castear la Fecha
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];
}
