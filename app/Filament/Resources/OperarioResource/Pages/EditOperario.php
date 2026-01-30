<?php

namespace App\Filament\Resources\OperarioResource\Pages;

use App\Filament\Resources\OperarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperario extends EditRecord
{
    protected static string $resource = OperarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
