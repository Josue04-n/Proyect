<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoPrendaResource\Pages;
use App\Models\TipoPrenda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Componentes del Formulario
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox; // <--- Usamos Checkbox como pediste

// Componentes de la Tabla
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class TipoPrendaResource extends Resource
{
    protected static ?string $model = TipoPrenda::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag'; // Icono de etiqueta

    protected static ?string $navigationLabel = 'Tipos de Prenda';

    protected static ?string $modelLabel = 'Tipo de Prenda';

    // Orden en el menú (Lo ponemos al final o al principio según prefieras)
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles de la Prenda')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. NOMBRE (Único)
                            TextInput::make('nombre')
                                ->label('Nombre de la Prenda')
                                ->placeholder('Ej: Saco, Pantalón, Chaleco...')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            // 2. ESTADO (Checkbox)
                            Checkbox::make('estado')
                                ->label('Habilitado para producción')
                                ->default(true),
                        ]),

                        // 3. DESCRIPCIÓN
                        Textarea::make('descripcion')
                            ->label('Descripción (Opcional)')
                            ->rows(3)
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50) // Corta el texto si es muy largo
                    ->toggleable(isToggledHiddenByDefault: false),

                // ESTADO (Icono Verde/Rojo)
                IconColumn::make('estado')
                    ->label('Activo')
                    ->boolean() // Convierte 1/0 en Check/X
                    ->sortable(),
                
                TextColumn::make('createdBy.name') 
                    ->label('Creado Por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updatedBy.name')
                    ->label('Actualizado Por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                
            ])
            ->filters([
                // Filtro para ver solo activos/inactivos
                TernaryFilter::make('estado')
                    ->label('Filtrar por Estado')
                    ->trueLabel('Habilitados')
                    ->falseLabel('Deshabilitados'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoPrendas::route('/'),
            'create' => Pages\CreateTipoPrenda::route('/create'),
            'edit' => Pages\EditTipoPrenda::route('/{record}/edit'),
        ];
    }
    
    public static function getRelations(): array
    {
        return [];
    }
}