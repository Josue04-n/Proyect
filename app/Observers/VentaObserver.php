<?php

namespace App\Observers;

use App\Models\Venta;
use App\Models\MovimientoCaja;
use App\Models\PrendaTienda;
use App\Models\DetalleVenta;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class VentaObserver
{
    /**
     * 
     */
    public function updated(Venta $venta): void
    {
        if ($venta->getOriginal('estado_pago') === 'pendiente' && $venta->estado_pago === 'pagado') {
            
            DB::transaction(function () use ($venta) {
                MovimientoCaja::create([
                    'fecha' => now(),
                    'tipo' => 'ingreso',
                    'monto' => $venta->total,
                    'origen_id' => $venta->id,
                    'origen_tipo' => 'App\Models\Venta',
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                $detalles = DetalleVenta::where('venta_id', $venta->id)->get();

                foreach ($detalles as $detalle) {
                    $producto = PrendaTienda::find($detalle->prenda_tienda_id);
                    
                    if ($producto) {
                        if ($producto->stock_actual < $detalle->cantidad) {
                            Notification::make()
                                ->title('Advertencia de Stock')
                                ->body("El producto {$producto->id} quedó en negativo.")
                                ->warning()
                                ->send();
                        }

                        $producto->decrement('stock_actual', $detalle->cantidad);
                    }
                }
            });
            
            Notification::make()
                ->title('Pago Registrado')
                ->body('Se actualizó el inventario y la caja correctamente.')
                ->success()
                ->send();
        }


    }
}