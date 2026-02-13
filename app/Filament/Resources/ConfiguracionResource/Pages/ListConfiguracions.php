<?php

namespace App\Filament\Resources\ConfiguracionResource\Pages;

use App\Filament\Resources\ConfiguracionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Configuracion;

class ListConfiguracions extends ListRecords
{
    protected static string $resource = ConfiguracionResource::class;

    protected function getHeaderActions(): array
    {
        // Lógica de "Singleton":
        // Si ya existe 1 registro, devolvemos un array vacío [] para ocultar el botón.
        if (Configuracion::count() >= 1) {
            return [];
        }

        // Si no existe, mostramos el botón para crear la primera vez.
        return [
            Actions\CreateAction::make()->label('Configurar Empresa'),
        ];
    }
}