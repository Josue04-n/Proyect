<?php

namespace App\Filament\Resources\EntregaProduccionResource\Pages;

use App\Filament\Resources\EntregaProduccionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use App\Models\EntregaProduccion;
use App\Models\AsignacionTrabajo;

class CreateEntregaProduccion extends CreateRecord
{
    protected static string $resource = EntregaProduccionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            DB::statement('CALL SP_REGISTRAR_ENTREGA_PRODUCCION(?, ?, ?, ?, ?, ?)', [
                $data['asignacion_trabajo_id'],
                $data['cantidad_entregada'],
                $data['fecha_recibo_real'],
                $data['tarifa_aplicada'],
                $data['observacion'] ?? null,
                auth()->id()
            ]);

            return EntregaProduccion::where('created_by', auth()->id())
                ->latest('id')
                ->first();

        } catch (QueryException $e) {
            
            if (str_contains($e->getMessage(), 'EXCESO')) {
                Notification::make()
                    ->title('Error de Cantidad')
                    ->body('No puedes recibir más prendas de las que fueron asignadas.')
                    ->danger()
                    ->send();
                $this->halt();
            }
            
            if (str_contains($e->getMessage(), 'No existe un local')) {
                Notification::make()
                    ->title('Error de Configuración')
                    ->body('No hay una sucursal marcada como "Principal" para recibir el stock.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            throw $e;
        }
    }
}