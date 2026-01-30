<?php

namespace App\Filament\Resources\PeriodoContableResource\Pages;

use App\Filament\Resources\PeriodoContableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodoContables extends ListRecords
{
    protected static string $resource = PeriodoContableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
