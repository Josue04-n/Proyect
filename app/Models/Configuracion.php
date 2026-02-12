<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'nombre_comercial',
        'direccion',
        'telefono',
        'email',
        'logo',
        'color_principal',
        'color_secundario',
    ];
}