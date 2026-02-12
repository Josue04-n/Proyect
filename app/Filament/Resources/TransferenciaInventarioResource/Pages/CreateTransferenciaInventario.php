<?php

namespace App\Filament\Resources\TransferenciaInventarioResource\Pages;

use App\Filament\Resources\TransferenciaInventarioResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\QueryException;
use App\Models\TransferenciaInventario;

class CreateTransferenciaInventario extends CreateRecord
{
    protected static string $resource = TransferenciaInventarioResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            // Llamamos al Procedure
            $result = DB::selectOne('CALL SP_REGISTRAR_TRANSFERENCIA(?, ?, ?, ?, ?, ?, ?)', [
                $data['origen_local_id'],
                $data['destino_local_id'],
                $data['prenda_tienda_id'],
                $data['cantidad'],
                $data['fecha_transferencia'],
                $data['observacion'] ?? null,
                auth()->id()
            ]);

            return TransferenciaInventario::find($result->id);

        } catch (QueryException $e) {
            // Capturamos el error de "STOCK INSUFICIENTE" del SQL
            if (str_contains($e->getMessage(), 'STOCK INSUFICIENTE')) {
                Notification::make()
                    ->title('Error de Inventario')
                    ->body('No hay suficiente stock en el local de origen para transferir esa cantidad.')
                    ->danger()
                    ->persistent()
                    ->send();
                
                $this->halt(); // Detenemos el proceso sin borrar el formulario
            }

            throw $e;
        }
    }
}