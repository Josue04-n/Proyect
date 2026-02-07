<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id',
        'prenda_tienda_id', // Producto de inventario
        'orden_produccion_id', // Opcional: si facturas una orden específica
        'tipo_prenda_id', // Para estadísticas rápidas
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function prendaTienda(): BelongsTo
    {
        return $this->belongsTo(PrendaTienda::class);
    }
    
    // Relación para obtener el nombre de la prenda (Camisa, Pantalón, etc.)
    public function tipoPrenda(): BelongsTo
    {
        return $this->belongsTo(TipoPrenda::class);
    }
}