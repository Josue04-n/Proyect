<?php

namespace App\Filament\Resources\PeriodoContableResource\Pages;

use App\Filament\Resources\PeriodoContableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeriodoContable extends EditRecord
{
    protected static string $resource = PeriodoContableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
