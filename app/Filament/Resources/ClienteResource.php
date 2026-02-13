<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// --- IMPORTACIONES CLAVE ---
use Filament\Forms\Get; // <--- NECESARIO para la lógica condicional
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $recordTitleAttribute = 'razon_social, primer_nombre, apellido_paterno';

    public static function getGloballySearchableAttributes(): array
    {
        return ['identificacion', 'razon_social', 'primer_nombre', 'apellido_paterno'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos Principales')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('tipo_cliente')
                                ->label('Tipo de Cliente')
                                ->options([
                                    'natural' => 'Persona Natural',
                                    'juridica' => 'Persona Jurídica (Empresa)',
                                ])
                                ->default('natural')
                                ->required()
                                ->live(),

                            TextInput::make('identificacion')
                                ->label('RUC / Cédula')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(13),
                                
                            Checkbox::make('is_active')
                                ->label('Cliente Activo')
                                ->default(true)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Información del Cliente')
                    ->schema([
                        TextInput::make('razon_social')
                            ->label('Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('tipo_cliente') === 'juridica'), // Condición

                        Grid::make(2)
                            ->schema([
                                TextInput::make('primer_nombre')->required()->maxLength(100),
                                TextInput::make('segundo_nombre')->maxLength(100),
                                TextInput::make('apellido_paterno')->required()->maxLength(100),
                                TextInput::make('apellido_materno')->maxLength(100),
                            ])
                            ->visible(fn (Get $get) => $get('tipo_cliente') === 'natural'), 
                    ]),

                Section::make('Contacto')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->label('Correo Electrónico')
                                ->email()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            TextInput::make('telefono')
                                ->label('Teléfono / Celular')
                                ->tel()
                                ->maxLength(10),

                            Textarea::make('direccion')
                                ->label('Dirección')
                                ->rows(2)
                                ->columnSpanFull()
                                ->maxLength(65535),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identificacion')
                    ->label('Identificación')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre_completo')
                    ->label('Cliente / Razón Social')
                    ->searchable(['primer_nombre', 'apellido_paterno', 'razon_social'])
                    ->sortable(['razon_social', 'apellido_paterno']),

                TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->toggleable(),

                TextColumn::make('createdBy.name')
                    ->label('Creado Por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updatedBy.name')
                    ->label('Actualizado Por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tipo_cliente')
                    ->badge()
                    ->colors([
                        'info' => 'natural',
                        'warning' => 'juridica',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'natural' => 'Persona',
                        'juridica' => 'Empresa',
                    }),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo_cliente')
                    ->options([
                        'natural' => 'Persona Natural',
                        'juridica' => 'Empresa',
                    ]),
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}