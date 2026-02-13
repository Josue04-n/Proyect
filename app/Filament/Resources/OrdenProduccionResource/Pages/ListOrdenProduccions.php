<?php

namespace App\Filament\Resources\OrdenProduccionResource\Pages;

use App\Filament\Resources\OrdenProduccionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrdenProduccions extends ListRecords
{
    protected static string $resource = OrdenProduccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
