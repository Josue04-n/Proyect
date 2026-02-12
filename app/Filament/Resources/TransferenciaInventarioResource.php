<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferenciaInventarioResource\Pages;
use App\Models\TransferenciaInventario;
use App\Models\PrendaTienda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get; 
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteAction;

class TransferenciaInventarioResource extends Resource
{
    protected static ?string $model = TransferenciaInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationLabel = 'Transferencias';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalle del Movimiento')
                    ->schema([
                        Grid::make(2)->schema([
                            // ORIGEN
                            Select::make('origen_local_id')
                                ->label('Desde (Origen)')
                                ->relationship('origenLocal', 'nombre')
                                ->required()
                                ->live() // Reactivo
                                ->afterStateUpdated(fn (callable $set) => $set('prenda_tienda_id', null)),

                            // DESTINO
                            Select::make('destino_local_id')
                                ->label('Hacia (Destino)')
                                ->relationship('destinoLocal', 'nombre')
                                ->required()
                                ->different('origen_local_id'), // No puede ser el mismo

                            // PRENDA (Filtrada)
                            Select::make('prenda_tienda_id')
                                ->label('Producto')
                                ->options(function (Get $get) {
                                    $origenId = $get('origen_local_id');
                                    if (!$origenId) return [];

                                    return PrendaTienda::where('local_id', $origenId)
                                        ->where('stock_actual', '>', 0)
                                        ->with('tipoPrenda')
                                        ->get()
                                        ->mapWithKeys(function ($item) {
                                            return [$item->id => "{$item->tipoPrenda->nombre} - {$item->talla} {$item->color} (Stock: {$item->stock_actual})"];
                                        });
                                })
                                ->searchable()
                                ->required()
                                ->live(),

                            // CANTIDAD
                            TextInput::make('cantidad')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->maxValue(function (Get $get) {
                                    $prendaId = $get('prenda_tienda_id');
                                    if (!$prendaId) return 99999;
                                    return PrendaTienda::find($prendaId)?->stock_actual ?? 0;
                                }),

                            DateTimePicker::make('fecha_transferencia')
                                ->default(now())
                                ->required(),
                        ]),

                        Textarea::make('observacion')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('origenLocal.nombre')
                    ->label('Origen')
                    ->icon('heroicon-m-arrow-right-start-on-rectangle')
                    ->sortable(),

                TextColumn::make('destinoLocal.nombre')
                    ->label('Destino')
                    ->icon('heroicon-m-arrow-right-end-on-rectangle')
                    ->sortable(),

                TextColumn::make('prendaTienda.tipoPrenda.nombre')
                    ->label('Prenda')
                    ->description(fn ($record) => "{$record->prendaTienda->talla} - {$record->prendaTienda->color}"),

                TextColumn::make('cantidad')
                    ->weight('bold')
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('fecha_transferencia')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                TextColumn::make('createdBy.name')
                    ->label('Resp.')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creación')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updatedBy.name')
                    ->label('Actualizó')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualización')
                    ->dateTime('d/m/Y H:i') 
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                DeleteAction::make()
                    ->label('Anular / Reversar')
                    ->modalHeading('¿Anular Transferencia?')
                    ->modalDescription('Esto devolverá el stock al origen y lo descontará del destino. Esta acción no se puede deshacer.')
                    ->action(function (TransferenciaInventario $record) {
                        try {
                            DB::statement('CALL SP_ANULAR_TRANSFERENCIA(?)', [$record->id]);
                            
                            Notification::make()
                                ->title('Transferencia Anulada')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al anular')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransferenciaInventarios::route('/'),
            'create' => Pages\CreateTransferenciaInventario::route('/create'),
        ];
    }
}