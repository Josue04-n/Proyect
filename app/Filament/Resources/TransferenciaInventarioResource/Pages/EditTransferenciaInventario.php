<?php

namespace App\Filament\Resources\TransferenciaInventarioResource\Pages;

use App\Filament\Resources\TransferenciaInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransferenciaInventario extends EditRecord
{
    protected static string $resource = TransferenciaInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
