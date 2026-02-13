<?php

namespace App\Filament\Resources\PrendaTiendaResource\Pages;

use App\Filament\Resources\PrendaTiendaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrendaTienda extends EditRecord
{
    protected static string $resource = PrendaTiendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
