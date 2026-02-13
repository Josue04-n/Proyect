<?php

namespace App\Filament\Resources\TransferenciaInventarioResource\Pages;

use App\Filament\Resources\TransferenciaInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransferenciaInventarios extends ListRecords
{
    protected static string $resource = TransferenciaInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
