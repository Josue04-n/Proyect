<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocalResource\Pages;
use App\Models\Local;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Componentes del Formulario
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Checkbox; // <--- CAMBIO IMPORTANTE

// Componentes de la Tabla
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class LocalResource extends Resource
{
    protected static ?string $model = Local::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Locales / Sucursales';

    protected static ?string $modelLabel = 'Local';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Local')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nombre')
                                ->label('Nombre del Local')
                                ->placeholder('Ej: Matriz, Sucursal Norte')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('telefono')
                                ->label('Teléfono de Contacto')
                                ->tel()
                                ->maxLength(20),

                            TextInput::make('direccion')
                                ->label('Dirección Principal')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('pasaje')
                                ->label('Pasaje / Referencia')
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Horarios y Configuración')
                    ->schema([
                        Grid::make(2)->schema([
                            TimePicker::make('hora_apertura')
                                ->label('Apertura')
                                ->seconds(false),

                            TimePicker::make('hora_cierre')
                                ->label('Cierre')
                                ->seconds(false),
                        ]),

                        Grid::make(2)->schema([
                            // --- AQUI EL CAMBIO A CHECKBOX ---
                            Checkbox::make('es_principal')
                                ->label('Es Sucursal Principal')
                                ->helperText('Marcar si es la casa matriz'),

                            Checkbox::make('is_active')
                                ->label('Local Operativo')
                                ->default(true),
                            // ---------------------------------
                        ]),
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
                    ->weight('bold')
                    ->description(fn (Local $record) => $record->es_principal ? '⭐ Matriz Principal' : null),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone'),

                TextColumn::make('hora_apertura')
                    ->label('Horario')
                    ->formatStateUsing(fn (Local $record) => 
                        ($record->hora_apertura && $record->hora_cierre) 
                        ? "{$record->hora_apertura} - {$record->hora_cierre}" 
                        : 'Sin horario'
                    )
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('createdBy.name')
                    ->label('Creado Por')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updatedBy.name')
                    ->label('Actualizado Por')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado Operativo')
                    ->trueLabel('Abiertos')
                    ->falseLabel('Cerrados'),
                
                TernaryFilter::make('es_principal')
                    ->label('Tipo de Sucursal')
                    ->trueLabel('Solo Matriz')
                    ->falseLabel('Sucursales'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocals::route('/'),
            'create' => Pages\CreateLocal::route('/create'),
            'edit' => Pages\EditLocal::route('/{record}/edit'),
        ];
    }
}