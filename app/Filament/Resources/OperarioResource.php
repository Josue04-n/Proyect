<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperarioResource\Pages;
use App\Models\Operario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Importaciones correctas de componentes
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter; 
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class OperarioResource extends Resource
{
    protected static ?string $model = Operario::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Operarios';

    protected static ?string $recordTitleAttribute = 'apellido_paterno';

    // CORRECCIÓN 1: El nombre correcto del método para búsqueda global
    public static function getGloballySearchableAttributes(): array
    {
        return ['cedula', 'primer_nombre', 'apellido_paterno'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identificación')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('cedula')
                                ->label('Cédula/DNI')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(10),

                            Checkbox::make('is_active')
                                ->label('Operario Activo')
                                ->default(true)
                                ->inline('center'),
                        ]),
                    ]),

                Section::make('Datos Personales')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->maxLength(150),

                            TextInput::make('segundo_nombre')
                                ->label('Segundo Nombre')
                                ->maxLength(150),

                            TextInput::make('apellido_paterno')
                                ->label('Apellido Paterno')
                                ->required()
                                ->maxLength(150),

                            TextInput::make('apellido_materno')
                                ->label('Apellido Materno')
                                ->maxLength(150),
                        ]),
                    ]),

                Section::make('Información de Contacto')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('telefono')
                                ->label('Teléfono')
                                ->tel()
                                ->prefix('+593 ')
                                ->prefixIcon('heroicon-o-phone')
                                ->maxLength(10),

                            Textarea::make('direccion')
                                ->label('Dirección Domiciliaria')
                                ->rows(3)
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cedula')
                    ->label('Cédula/DNI')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre_completo')
                    ->label('Nombre Completo')
                    ->searchable(['primer_nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno'])
                    ->sortable(['apellido_paterno']), 

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->sortable()
                    ->searchable()
                    ->toggleable(), 

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true), 
                
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Filtrar por estado')
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperarios::route('/'),
            'create' => Pages\CreateOperario::route('/create'),
            'edit' => Pages\EditOperario::route('/{record}/edit'),
        ];
    }
}