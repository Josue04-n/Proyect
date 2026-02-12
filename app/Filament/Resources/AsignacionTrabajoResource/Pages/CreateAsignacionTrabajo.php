<?php

namespace App\Filament\Resources\AsignacionTrabajoResource\Pages;

use App\Filament\Resources\AsignacionTrabajoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use App\Models\AsignacionTrabajo;

class CreateAsignacionTrabajo extends CreateRecord
{
    protected static string $resource = AsignacionTrabajoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            DB::statement('CALL SP_ASIGNAR_TRABAJO(?, ?, ?, ?, ?, ?)', [
                $data['orden_item_id'],
                $data['operario_id'],
                $data['cantidad_asignada'],
                $data['fecha_estimada_entrega'],
                $data['observacion'] ?? null,
                auth()->id()
            ]);

            return AsignacionTrabajo::where('created_by', auth()->id())
                ->latest('id')
                ->first();

        } catch (QueryException $e) {
            
            if (str_contains($e->getMessage(), 'EXCESO DE ASIGNACIÓN')) {
                Notification::make()
                    ->title('Límite Excedido')
                    ->body('Estás intentando asignar más prendas de las que el cliente pidió. Revisa la cantidad.')
                    ->danger()
                    ->persistent()
                    ->send();
                
                $this->halt(); 
            }

            throw $e;
        }
    }
}