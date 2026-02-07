<?php

namespace App\Filament\Resources\PrendaTiendaResource\Pages;

use App\Filament\Resources\PrendaTiendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrendaTiendas extends ListRecords
{
    protected static string $resource = PrendaTiendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
