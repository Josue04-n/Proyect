<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InsumoResource\Pages;
use App\Models\Insumo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Componentes del Formulario
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;

// Componentes de la Tabla
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class InsumoResource extends Resource
{
    protected static ?string $model = Insumo::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box'; // Icono de caja

    protected static ?string $navigationLabel = 'Insumos / Materiales';

    protected static ?string $modelLabel = 'Insumo';

    protected static ?int $navigationSort = 5; // En el menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Material')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. NOMBRE
                            TextInput::make('nombre')
                                ->label('Nombre del Insumo')
                                ->placeholder('Ej: Tela Jean Azul, Hilo Blanco 40/2')
                                ->required()
                                ->maxLength(255),

                            // 2. UNIDAD DE MEDIDA
                            Select::make('unidad_medida')
                                ->label('Unidad de Medida')
                                ->options([
                                    'metros' => 'Metros',
                                    'conos' => 'Conos',
                                    'unidades' => 'Unidades (Piezas)',
                                    'kilos' => 'Kilogramos',
                                    'docenas' => 'Docenas',
                                    'yardas' => 'Yardas',
                                    'rollos' => 'Rollos',
                                ])
                                ->searchable()
                                ->required()
                                ->createOptionForm([ // Opcional: permitir crear al vuelo si quisieras
                                    TextInput::make('unidad')->required(),
                                ])
                                ->createOptionUsing(fn ($data) => $data['unidad']), 

                            // 3. COSTO
                            TextInput::make('costo_promedio')
                                ->label('Costo Promedio (Referencial)')
                                ->numeric()
                                ->prefix('$')
                                ->default(0),

                            // 4. ESTADO (Checkbox)
                            Checkbox::make('estado')
                                ->label('Insumo Activo')
                                ->default(true)
                                ->inline(false), // Para alinearlo mejor
                        ]),
                    ]),

                Section::make('Control de Inventario')
                    ->schema([
                        Grid::make(2)->schema([
                            // 5. STOCK ACTUAL
                            TextInput::make('stock_actual')
                                ->label('Stock Actual')
                                ->numeric()
                                ->default(0)
                                ->required(),

                            // 6. STOCK MÍNIMO (Para alertas)
                            TextInput::make('stock_minimo')
                                ->label('Stock Mínimo (Alerta)')
                                ->helperText('El sistema avisará cuando el stock sea menor a este número')
                                ->numeric()
                                ->default(5)
                                ->required(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Insumo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('unidad_medida')
                    ->label('Unidad')
                    ->badge() // Se ve bonito como etiqueta
                    ->color('gray'),

                // STOCK CON ALERTA DE COLOR
                TextColumn::make('stock_actual')
                    ->label('Stock')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (Insumo $record): string => 
                        $record->stock_actual <= $record->stock_minimo ? 'danger' : 'success'
                    )
                    ->icon(fn (Insumo $record): string => 
                        $record->stock_actual <= $record->stock_minimo ? 'heroicon-m-exclamation-triangle' : ''
                    ),

                TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('costo_promedio')
                    ->label('Costo')
                    ->money('USD')
                    ->sortable(),

                IconColumn::make('estado')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('createdBy.name')
                    ->label('Creado Por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updatedBy.name')
                    ->label('Actualizado Por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                // FILTRO: Ver productos con Stock Bajo
                Filter::make('stock_bajo')
                    ->label('⚠️ Stock Bajo / Crítico')
                    ->query(fn (Builder $query) => $query->whereColumn('stock_actual', '<=', 'stock_minimo'))
                    ->toggle(), // Switch simple en el filtro
                
                TernaryFilter::make('estado')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
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
            'index' => Pages\ListInsumos::route('/'),
            'create' => Pages\CreateInsumo::route('/create'),
            'edit' => Pages\EditInsumo::route('/{record}/edit'),
        ];
    }
}