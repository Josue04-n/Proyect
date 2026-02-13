<?php

namespace App\Filament\Resources\PagoOperarioResource\Pages;

use App\Filament\Resources\PagoOperarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPagoOperarios extends ListRecords
{
    protected static string $resource = PagoOperarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
