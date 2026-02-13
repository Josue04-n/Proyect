<?php

namespace App\Filament\Resources\TipoPrendaResource\Pages;

use App\Filament\Resources\TipoPrendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoPrendas extends ListRecords
{
    protected static string $resource = TipoPrendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
