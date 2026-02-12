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
use Filament\Forms\Components\Select; 
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ColorColumn;

class ConfiguracionResource extends Resource
{
    protected static ?string $model = Configuracion::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth'; 
    protected static ?string $navigationLabel = 'Datos de Empresa';
    protected static ?string $modelLabel = 'Configuración';
    protected static ?int $navigationSort = 100; 

    public static function canCreate(): bool
    {
        return Configuracion::count() === 0;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    
                    // SECCIÓN 1: DATOS
                    Section::make('Información del Negocio')
                        ->icon('heroicon-o-building-storefront')
                        ->schema([
                            FileUpload::make('logo')
                                ->label('Logo')
                                ->image()
                                ->avatar() // Visualización redonda en el formulario
                                ->disk('public') // Guarda en storage/app/public
                                ->directory('logos_empresa')
                                ->visibility('public')
                                // --- OPTIMIZACIÓN ---
                                ->maxSize(2048) // Máximo 2MB (Para que no suban fotos pesadas)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']) // Solo formatos web
                                ->imageEditor() // Permite recortar antes de subir
                                // --------------------
                                ->columnSpanFull()
                                ->alignCenter(),
                            
                            TextInput::make('nombre_comercial')
                                ->label('Nombre de la Empresa')
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('direccion')
                                ->label('Dirección')
                                ->maxLength(255),

                            TextInput::make('telefono')
                                ->label('Teléfono')
                                ->tel(),

                            TextInput::make('email')
                                ->label('Correo Electrónico')
                                ->email(),
                        ])->columnSpan(1),

                    Section::make('Apariencia')
    ->icon('heroicon-o-paint-brush')
    ->schema([
        
        // 1. SELECTOR DE TEMAS (PRESETS)
        Select::make('tema_preset')
            ->label('Paleta de Colores Recomendada')
            ->options([
                'default' => 'Filament Default (Ámbar)',
                'ocean'   => 'Océano Profesional (Azul Profundo)',
                'forest'  => 'Bosque Sereno (Verde)',
                'royal'   => 'Realeza (Púrpura)',
                'sunset'  => 'Atardecer (Naranja Corporativo)',
                'dark'    => 'Modo Oscuro Puro (Grises)',
            ])
            ->placeholder('Selecciona un estilo...')
            ->live() // ¡La magia ocurre aquí! Hace que reaccione al instante
            ->afterStateUpdated(function (Set $set, ?string $state) {
                // Aquí definimos los colores hexadecimales para cada tema
                match ($state) {
                    'default' => [
                        $set('color_principal', '#F59E0B'), // Amber-500
                        $set('color_secundario', '#64748B'), // Slate-500
                    ],
                    'ocean' => [
                        $set('color_principal', '#0EA5E9'), // Sky-500
                        $set('color_secundario', '#1E293B'), // Slate-800
                    ],
                    'forest' => [
                        $set('color_principal', '#10B981'), // Emerald-500
                        $set('color_secundario', '#064E3B'), // Emerald-900
                    ],
                    'royal' => [
                        $set('color_principal', '#8B5CF6'), // Violet-500
                        $set('color_secundario', '#4C1D95'), // Violet-900
                    ],
                    'sunset' => [
                        $set('color_principal', '#F97316'), // Orange-500
                        $set('color_secundario', '#7C2D12'), // Orange-900
                    ],
                    'dark' => [
                        $set('color_principal', '#64748B'), // Slate-500
                        $set('color_secundario', '#0F172A'), // Slate-900
                    ],
                    default => null,
                };
            })
            ->columnSpanFull(), // Que ocupe todo el ancho para separar

        // 2. LOS PICKERS (Se llenan solos o manual)
        ColorPicker::make('color_principal')
            ->label('Color Principal (Botones, Links)')
            ->required(),
        
        ColorPicker::make('color_secundario')
            ->label('Color Secundario (Bordes, Fondos)')
            ->required(),

    ])->columnSpan(1),        


                    Section::make('Apariencia')
                        ->icon('heroicon-o-paint-brush')
                        ->schema([
                            ColorPicker::make('color_principal')
                                ->label('Color Principal')
                                ->required(),
                            
                            ColorPicker::make('color_secundario')
                                ->label('Color Secundario')
                                ->required(),
                        ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // AQUÍ ESTABA EL DETALLE DE VISUALIZACIÓN
                ImageColumn::make('logo')
                    ->circular()
                    ->disk('public') // Obligatorio para leer de storage/app/public
                    ->visibility('public'),
                
                TextColumn::make('nombre_comercial')
                    ->label('Empresa')
                    ->weight('bold')
                    ->description(fn (Configuracion $record) => $record->email),
                
                ColorColumn::make('color_principal')
                    ->label('Color P.'),

                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i'),
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