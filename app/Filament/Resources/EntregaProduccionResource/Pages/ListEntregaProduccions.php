<?php

namespace App\Filament\Resources\EntregaProduccionResource\Pages;

use App\Filament\Resources\EntregaProduccionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEntregaProduccions extends ListRecords
{
    protected static string $resource = EntregaProduccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
