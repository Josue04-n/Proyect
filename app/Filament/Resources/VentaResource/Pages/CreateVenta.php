<?php

namespace App\Filament\Resources\VentaResource\Pages;

use App\Filament\Resources\VentaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Filament\Notifications\Notification;

class CreateVenta extends CreateRecord
{
    protected static string $resource = VentaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $detalles = array_values($data['detalles'] ?? []);
        
        $detallesProcesados = array_map(function($item) {
            return [
                'prenda_tienda_id' => (int) $item['prenda_tienda_id'],
                'cantidad'         => (int) $item['cantidad'],
                'precio_unitario'  => (float) $item['precio_unitario'],
                'subtotal'         => (float) $item['subtotal'],
            ];
        }, $detalles);

        $detallesJson = json_encode($detallesProcesados);

        // Obtenemos el local_id: PRIORIDAD 1: Del formulario, PRIORIDAD 2: Del usuario logueado
        $localId = $data['local_id'] ?? auth()->user()->local_id;

        try {
            // AHORA SON 11 SIGNOS DE INTERROGACIÓN
            $result = DB::selectOne('CALL SP_REGISTRAR_VENTA(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $data['cliente_id'],        // 1
                $data['subtotal'],          // 2
                $data['descuento'] ?? 0,    // 3
                $data['impuestos'] ?? 0,    // 4
                $data['total'],             // 5
                $data['estado_pago'],       // 6
                $data['metodo_pago'],       // 7
                $data['requiere_factura'] ?? false, // 8
                auth()->id(),               // 9 (p_user_id)
                $localId,                   // 10 (p_local_id) <- EL NUEVO PARÁMETRO
                $detallesJson               // 11 (p_detalles)
            ]);

            return Venta::find($result->id);

        } catch (QueryException $e) {
            // Manejo de errores personalizados desde el Procedure (SIGNAL SQLSTATE)
            $message = $e->getMessage();

            if (str_contains($message, 'STOCK INSUFICIENTE')) {
                Notification::make()
                    ->title('Error de Stock')
                    ->body('No hay suficiente stock disponible para realizar esta venta.')
                    ->danger()
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error en la base de datos')
                    ->body('Ocurrió un problema técnico: ' . $message)
                    ->danger()
                    ->send();
            }

            $this->halt();
        }
    }
}