<?php

namespace App\Filament\Resources\TarifaResource\Pages;

use App\Filament\Resources\TarifaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTarifas extends ListRecords
{
    protected static string $resource = TarifaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
