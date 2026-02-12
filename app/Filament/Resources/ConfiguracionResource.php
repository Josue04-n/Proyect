<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfiguracionResource\Pages;
use App\Models\Configuracion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Componentes
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class ConfiguracionResource extends Resource
{
    protected static ?string $model = Configuracion::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth'; 
    protected static ?string $navigationLabel = 'Datos de Empresa';
    protected static ?string $modelLabel = 'Configuración';
    protected static ?int $navigationSort = 100; 

    // Solo permitir crear si no existe ningún registro
    public static function canCreate(): bool
    {
        return Configuracion::count() === 0;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Usamos un Grid simple para centrar todo
                Grid::make(1)->schema([
                    
                    Section::make('Información del Negocio')
                        ->description('Estos datos aparecerán en los reportes y cabeceras.')
                        ->icon('heroicon-o-building-storefront')
                        ->schema([
                            
                            // LOGO
                            FileUpload::make('logo')
                                ->label('Logo Oficial')
                                ->image()
                                ->avatar() // Redondo en la vista previa
                                ->disk('public')
                                ->directory('logos_empresa')
                                ->visibility('public')
                                ->maxSize(2048) // Max 2MB
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->columnSpanFull()
                                ->alignCenter(),
                            
                            // DATOS EN DOS COLUMNAS
                            Grid::make(2)->schema([
                                TextInput::make('nombre_comercial')
                                    ->label('Nombre de la Empresa')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(), // Nombre ocupa todo el ancho
                                
                                TextInput::make('direccion')
                                    ->label('Dirección')
                                    ->maxLength(255),

                                TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->tel(),

                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->columnSpanFull(),
                            ]),
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->circular()
                    ->disk('public')
                    ->visibility('public'),
                
                TextColumn::make('nombre_comercial')
                    ->label('Empresa')
                    ->weight('bold')
                    ->description(fn (Configuracion $record) => $record->direccion),

                TextColumn::make('telefono')
                    ->label('Contacto')
                    ->icon('heroicon-m-phone'),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope'),

                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfiguracions::route('/'),
            'create' => Pages\CreateConfiguracion::route('/create'),
            'edit' => Pages\EditConfiguracion::route('/{record}/edit'),
        ];
    }
}