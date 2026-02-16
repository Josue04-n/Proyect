<?php

namespace App\Filament\Widgets;

use App\Models\PrendaTienda;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

class StockCriticoTable extends BaseWidget
{
    protected static ?string $heading = '⚠️ Alerta de Stock Crítico (Menos de 5 unidades)';
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full'; // Ocupa todo el ancho para leer bien la tabla

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(
                PrendaTienda::query()
                    ->where('stock_actual', '<=', 5) // Definimos el límite crítico
                    ->when(!$user->hasRole('super_admin'), function (Builder $query) use ($user) {
                        return $query->where('local_id', $user->local_id);
                    })
                    ->orderBy('stock_actual', 'asc')
            )
            ->columns([
                TextColumn::make('tipoPrenda.nombre')
                    ->label('Producto')
                    ->searchable(),
                
                TextColumn::make('local.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('gray')
                    ->visible(fn () => $user->hasRole('super_admin')),

                TextColumn::make('talla')
                    ->label('Talla'),

                TextColumn::make('color')
                    ->label('Color'),

                TextColumn::make('stock_actual')
                    ->label('Stock')
                    ->weight('bold')
                    ->color(fn (int $state): string => $state <= 2 ? 'danger' : 'warning')
                    ->icon(fn (int $state): string => $state <= 2 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-information-circle'),
            ])
            ->actions([
                Action::make('Ver Producto')
                    ->url(fn (PrendaTienda $record): string => "/admin/prenda-tiendas/{$record->id}/edit")
                    ->icon('heroicon-m-eye')
                    ->button(),
            ]);
    }
}