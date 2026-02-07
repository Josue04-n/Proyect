<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedorResource\Pages;
use App\Models\Proveedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2'; // Icono de edificio

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?int $navigationSort = 10; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos de la Empresa')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('razon_social')
                                ->label('Razón Social / Empresa')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('ruc')
                                ->label('RUC / Identificación')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(13),
                        ]),
                    ]),

                Section::make('Información de Contacto')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('contacto')
                                ->label('Nombre del Contacto')
                                ->placeholder('Ej: Sr. Juan Pérez')
                                ->maxLength(255),

                            TextInput::make('telefono')
                                ->label('Teléfono')
                                ->tel()
                                ->maxLength(10),

                            TextInput::make('email')
                                ->label('Correo Electrónico')
                                ->email()
                                ->maxLength(255),
                        ]),
                        
                        Checkbox::make('is_active')
                            ->label('Proveedor Activo')
                            ->default(true)
                            ->helperText('Desmarcar si ya no trabajamos con este proveedor.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('razon_social')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('ruc')
                    ->label('RUC')
                    ->searchable()
                    ->copyable(), 

                TextColumn::make('contacto')
                    ->label('Contacto')
                    ->searchable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone'),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
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
            'index' => Pages\ListProveedors::route('/'),
            'create' => Pages\CreateProveedor::route('/create'),
            'edit' => Pages\EditProveedor::route('/{record}/edit'),
        ];
    }
}