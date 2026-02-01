<?php

namespace App\Filament\Resources\AsignacionTrabajoResource\Pages;

use App\Filament\Resources\AsignacionTrabajoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsignacionTrabajo extends EditRecord
{
    protected static string $resource = AsignacionTrabajoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
