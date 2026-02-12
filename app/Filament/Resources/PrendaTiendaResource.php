<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrendaTiendaResource\Pages;
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

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class PrendaTiendaResource extends Resource
{
    protected static ?string $model = PrendaTienda::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag'; 

    protected static ?string $navigationLabel = 'Inventario en Tienda';

    protected static ?string $modelLabel = 'Prenda en Venta';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos del Producto')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. UBICACIÓN
                            Select::make('local_id')
                                ->label('Local / Sucursal')
                                ->relationship('local', 'nombre')
                                ->searchable()
                                ->preload()
                                ->required(),

                            // 2. PRODUCTO
                            Select::make('tipo_prenda_id')
                                ->label('Tipo de Prenda')
                                ->relationship('tipoPrenda', 'nombre')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('talla')
                                ->label('Talla')
                                ->required()
                                ->maxLength(10),

                            TextInput::make('color')
                                ->label('Color')
                                ->maxLength(50),

                            TextInput::make('stock_actual')
                                ->label('Stock Inicial')
                                ->numeric()
                                ->default(0)
                                ->required(),
                        ]),

                        TextInput::make('precio_venta')
                            ->label('Precio de Venta (PVP)')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('local.nombre')
                    ->label('Sucursal')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('tipoPrenda.nombre')
                    ->label('Prenda')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (PrendaTienda $record) => "{$record->talla} - {$record->color}"),

                // STOCK con alerta visual
                TextColumn::make('stock_actual')
                    ->label('Stock')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn ($state) => $state <= 2 ? 'danger' : 'success')
                    ->weight('bold'),

                // PRECIO EDITABLE EN TABLA (Muy útil para actualizar rápido)
                TextColumn::make('precio_venta')
                    ->label('Precio ($)')
                    ->sortable(),
                
                TextColumn::make('createdBy.name')
                    ->label('Creado por')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updatedBy.name')
                    ->label('Actualizado por')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro por Sucursal
                SelectFilter::make('local_id')
                    ->label('Filtrar por Sucursal')
                    ->relationship('local', 'nombre'),

                // Filtro por Tipo de Ropa
                SelectFilter::make('tipo_prenda_id')
                    ->label('Filtrar por Prenda')
                    ->relationship('tipoPrenda', 'nombre'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrendaTiendas::route('/'),
            'create' => Pages\CreatePrendaTienda::route('/create'),
            'edit' => Pages\EditPrendaTienda::route('/{record}/edit'),
        ];
    }
}