<?php

namespace App\Filament\Resources\VentaResource\Pages;

use App\Filament\Resources\VentaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

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

        try {
            $result = DB::selectOne('CALL SP_REGISTRAR_VENTA(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $data['cliente_id'],
                $data['subtotal'],
                $data['descuento'] ?? 0,
                $data['impuestos'] ?? 0,
                $data['total'],
                $data['estado_pago'],
                $data['metodo_pago'],
                $data['requiere_factura'] ?? false,
                auth()->id(),
                $detallesJson
            ]);

            return Venta::find($result->id);

        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'STOCK INSUFICIENTE')) {
                
                Notification::make()
                    ->title('Error de Stock')
                    ->body('No hay suficiente stock disponible para realizar esta venta.')
                    ->danger() 
                    ->persistent() 
                    ->send();

                $this->halt();
            }

            throw $e;
        }
    }
}