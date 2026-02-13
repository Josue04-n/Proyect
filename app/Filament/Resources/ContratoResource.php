<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContratoResource\Pages;
use App\Models\Contrato;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

// Componentes de la Tabla
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class ContratoResource extends Resource
{
    protected static ?string $model = Contrato::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Contratos';

    protected static ?string $modelLabel = 'Contrato';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. CÓDIGO (Generación Automática en el Formulario)
                            TextInput::make('codigo_contrato')
                                ->label('Código del Contrato')
                                ->default(fn () => 'CTR-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                                ->readOnly() // El usuario lo ve pero no lo edita
                                ->dehydrated() // <--- CRÍTICO: Obliga a guardar el dato aunque sea readOnly
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),

                            // 2. CLIENTE (Buscador Inteligente)
                            Select::make('cliente_id')
                                ->label('Cliente')
                                ->relationship('cliente', 'identificacion')
                                ->getOptionLabelFromRecordUsing(fn ($record) => 
                                    $record->tipo_cliente === 'juridica'
                                        ? "{$record->razon_social} - {$record->identificacion}"
                                        : "{$record->primer_nombre} {$record->apellido_paterno} - {$record->identificacion}"
                                )
                                ->searchable(['primer_nombre', 'apellido_paterno', 'razon_social', 'identificacion'])
                                ->preload()
                                ->required(),
                        ]),
                    ]),

                Section::make('Detalles y Vigencia')
                    ->schema([
                        Grid::make(3)->schema([
                            // 3. PRESUPUESTO
                            TextInput::make('presupuesto_total')
                                ->label('Presupuesto Total')
                                ->numeric()
                                ->prefix('$')
                                ->required(),

                            // 4. FECHAS
                            DatePicker::make('fecha_inicio')
                                ->label('Fecha de Inicio')
                                ->default(now())
                                ->required(),

                            DatePicker::make('fecha_fin_estimada')
                                ->label('Fecha Fin (Estimada)')
                                ->afterOrEqual('fecha_inicio'),
                        ]),

                        // 5. ESTADO
                        Select::make('estado')
                            ->label('Estado del Contrato')
                            ->options([
                                'vigente' => 'Vigente',
                                'finalizado' => 'Finalizado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('vigente')
                            ->required()
                            ->native(false),
                            
                        // 6. DESCRIPCIÓN
                        Textarea::make('descripcion')
                            ->label('Descripción / Cláusulas Resumidas')
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
                TextColumn::make('codigo_contrato')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Usa el atributo virtual del modelo Cliente
                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->searchable(['primer_nombre', 'apellido_paterno', 'razon_social'])
                    ->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_fin_estimada')
                    ->label('Fin Est.')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('presupuesto_total')
                    ->label('Presupuesto')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vigente' => 'success',
                        'finalizado' => 'info',
                        'cancelado' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'vigente' => 'heroicon-m-check-circle',
                        'finalizado' => 'heroicon-m-archive-box',
                        'cancelado' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    }),
                
                TextColumn::make('createdBy.name')
                    ->label('Creado Por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'vigente' => 'Vigente',
                        'finalizado' => 'Finalizado',
                        'cancelado' => 'Cancelado',
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContratos::route('/'),
            'create' => Pages\CreateContrato::route('/create'),
            'edit' => Pages\EditContrato::route('/{record}/edit'),
        ];
    }
    
    public static function getRelations(): array
    {
        return [];
    }
}