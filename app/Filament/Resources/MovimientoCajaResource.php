<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoCajaResource\Pages;
use App\Models\MovimientoCaja;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ViewAction;

class MovimientoCajaResource extends Resource
{
    protected static ?string $model = MovimientoCaja::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale'; // Balanza o Billetera
    protected static ?string $navigationLabel = 'Movimientos de Caja';
    protected static ?string $modelLabel = 'Movimiento';
    protected static ?int $navigationSort = 99; // Al final del menú

    // --- BLOQUEO TOTAL DE EDICIÓN ---
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    // --------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success', // Verde
                        'egreso' => 'danger',   // Rojo
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'ingreso' => 'heroicon-m-arrow-trending-up',
                        'egreso' => 'heroicon-m-arrow-trending-down',
                    }),

                TextColumn::make('origen_tipo')
                    ->label('Concepto')
                    ->formatStateUsing(function ($state, $record) {
                        // Traducimos el nombre técnico a algo legible
                        return match ($state) {
                            'App\Models\Venta' => 'Venta #' . ($record->origen->id ?? 'N/A'),
                            'App\Models\PagoOperario' => 'Pago Nómina',
                            'App\Models\Compra' => 'Compra Insumos',
                            default => 'Movimiento',
                        };
                    })
                    ->description(fn ($record) => match ($record->origen_tipo) {
                        'App\Models\Venta' => $record->origen?->cliente?->razon_social ?? 'Cliente Final',
                        'App\Models\PagoOperario' => $record->origen?->operario?->primer_nombre ?? 'Operario',
                        default => '',
                    }),

                TextColumn::make('monto')
                    ->money('USD')
                    ->weight('bold')
                    ->alignRight()
                    // COLUMNA DE SUMATORIA (TOTAL AL PIE DE PAGINA)
                    // Puedes agregar summarize() si quieres ver el total de la página actual
                   , 

                TextColumn::make('creador.name')
                    ->label('Responsable')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'ingreso' => 'Ingresos (Ventas)',
                        'egreso' => 'Egresos (Gastos)',
                    ]),
                
                Filter::make('fecha')
                    ->form([
                        DatePicker::make('desde')->default(now()),
                        DatePicker::make('hasta')->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '<=', $date),
                            );
                    })
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]); // Sin acciones masivas
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimientoCajas::route('/'),
        ];
    }
}