<?php

namespace App\Filament\Resources\OrdenProduccionResource\Pages;

use App\Filament\Resources\OrdenProduccionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdenProduccion extends EditRecord
{
    protected static string $resource = OrdenProduccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
