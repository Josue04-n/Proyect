<?php

namespace App\Filament\Resources\TipoPrendaResource\Pages;

use App\Filament\Resources\TipoPrendaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoPrenda extends EditRecord
{
    protected static string $resource = TipoPrendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
