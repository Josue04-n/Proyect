<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TarifaResource\Pages;
use App\Models\Tarifa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Checkbox;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class TarifaResource extends Resource
{
    protected static ?string $model = Tarifa::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar'; // Icono de dinero

    protected static ?string $navigationLabel = 'Tarifas de Pago';

    protected static ?string $modelLabel = 'Tarifa';

    protected static ?int $navigationSort = 4; // Después de las prendas

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuración de Tarifa')
                    ->schema([
                        Grid::make(2)->schema([
                            // SELECTOR DE PRENDA
                            Select::make('tipo_prenda_id')
                                ->label('Tipo de Prenda')
                                ->relationship('tipoPrenda', 'nombre')
                                ->searchable()
                                ->preload()
                                ->required(),

                            // PRECIO
                            TextInput::make('precio_mano_obra')
                                ->label('Pago por Unidad')
                                ->numeric()
                                ->prefix('$')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            DatePicker::make('vigencia_desde')
                                ->label('Vigente Desde')
                                ->default(now())
                                ->required(),

                            DatePicker::make('vigencia_hasta')
                                ->label('Vigente Hasta')
                                ->placeholder('Indefinido')
                                ->afterOrEqual('vigencia_desde'),
                        ]),

                        // --- CAMBIO A CHECKBOX ---
                        Checkbox::make('estado')
                            ->label('Tarifa Activa')
                            ->default(true),
                        // -------------------------
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Mostramos el nombre de la prenda, no el ID
                TextColumn::make('tipoPrenda.nombre')
                    ->label('Prenda')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('precio_mano_obra')
                    ->label('Pago x Unidad')
                    ->money('USD') // Formato moneda automático
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('vigencia_desde')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('vigencia_hasta')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->placeholder('Indefinido')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('estado')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('createdBy.name')
                    ->label('Creado Por')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updatedBy.name')
                    ->label('Actualizado Por')
                    ->toggleable(isToggledHiddenByDefault: true),  

                TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_prenda_id')
                    ->relationship('tipoPrenda', 'nombre')
                    ->label('Filtrar por Prenda'),
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
            'index' => Pages\ListTarifas::route('/'),
            'create' => Pages\CreateTarifa::route('/create'),
            'edit' => Pages\EditTarifa::route('/{record}/edit'),
        ];
    }
}