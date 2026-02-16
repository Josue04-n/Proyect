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
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\User;
use App\Models\Local;


class MovimientoCajaResource extends Resource
{
    protected static ?string $model = MovimientoCaja::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale'; 
    protected static ?string $navigationLabel = 'Movimientos de Caja';
    protected static ?string $modelLabel = 'Movimiento';
    protected static ?int $navigationSort = 99; 

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

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
                        'INGRESO' => 'success', 
                        'EGRESO' => 'danger',   
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'INGRESO' => 'heroicon-m-arrow-trending-up',
                        'EGRESO' => 'heroicon-m-arrow-trending-down',
                    }),

                TextColumn::make('local.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('origen_tipo')
                    ->label('Concepto')
                    ->formatStateUsing(function ($state, $record) {
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
                    ->alignRight(), 

                TextColumn::make('creador.name')
                    ->label('Responsable')
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'INGRESO' => 'Ingresos (Ventas)',
                        'EGRESO' => 'Egresos (Gastos)',
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
            ->bulkActions([]); 
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimientoCajas::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user->hasRole('super_admin')) {
            return $query->where('local_id', $user->local_id);
        }

        return $query;
    }

}