<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsignacionTrabajoResource\Pages;
use App\Models\AsignacionTrabajo;
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
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\SelectColumn;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class AsignacionTrabajoResource extends Resource
{
    protected static ?string $model = AsignacionTrabajo::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Asignaciones de Trabajo';

    protected static ?string $modelLabel = 'Asignación';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalle de la Asignación')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. SELECCIÓN INTELIGENTE DEL TRABAJO
                            Select::make('orden_item_id')
                                ->label('Item a Producir')
                                ->relationship('ordenItem', 'id') 
                                ->getOptionLabelFromRecordUsing(fn ($record) => 
                                    "Orden #{$record->ordenProduccion->id} - {$record->tipoPrenda->nombre} ({$record->talla} / {$record->color}) - Pendientes: {$record->cantidad}"
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpanFull(), 

                            // 2. OPERARIO
                            Select::make('operario_id') 
                                ->label('Operario Responsable')
                                ->relationship('operario', 'primer_nombre') 
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->primer_nombre} {$record->apellido_paterno}")
                                ->searchable(['primer_nombre', 'apellido_paterno'])
                                ->preload()
                                ->required(),

                            // 3. CANTIDAD
                            TextInput::make('cantidad_asignada')
                                ->label('Cantidad a Asignar')
                                ->numeric()
                                ->required()
                                ->minValue(1),
                        ]),

                        Grid::make(2)->schema([
                            DatePicker::make('fecha_asignacion')
                                ->label('Fecha Asignación')
                                ->default(now())
                                ->required(),

                            DatePicker::make('fecha_estimada_entrega')
                                ->label('Fecha Entrega (Est.)')
                                ->afterOrEqual('fecha_asignacion'),
                        ]),

                        // ESTADO
                        Select::make('estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'en_proceso' => 'En Proceso',
                                'completada' => 'Completada',
                                'cancelada' => 'Cancelada',
                            ])
                            ->default('pendiente')
                            ->required(),

                        Textarea::make('observacion')
                            ->label('Instrucciones para el Operario')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna combinada para ahorrar espacio
                TextColumn::make('ordenItem.ordenProduccion.id')
                    ->label('Orden')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "#{$state}")
                    ->weight('bold'),

                TextColumn::make('operario.primer_nombre') 
                    ->label('Operario')
                    ->sortable()
                    ->searchable(['primer_nombre', 'apellido_paterno']) 
                    ->formatStateUsing(fn ($record) => "{$record->operario->primer_nombre} {$record->operario->apellido_paterno}"),

                // Descripción de qué está haciendo
                TextColumn::make('ordenItem.tipoPrenda.nombre')
                    ->label('Prenda')
                    ->description(fn ($record) => "{$record->ordenItem->talla} - {$record->ordenItem->color}"),

                TextColumn::make('cantidad_asignada')
                    ->label('Cant.')
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('fecha_estimada_entrega')
                    ->label('Entrega')
                    ->date('d/m')
                    ->sortable(),

                // Estado editable directo en la tabla
                SelectColumn::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                    ])
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
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'completada' => 'Completada',
                    ]),
                
                SelectFilter::make('operario_id')
                    ->label('Filtrar por Operario')
                    ->relationship('operario', 'primer_nombre'),
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
            'index' => Pages\ListAsignacionTrabajos::route('/'),
            'create' => Pages\CreateAsignacionTrabajo::route('/create'),
            'edit' => Pages\EditAsignacionTrabajo::route('/{record}/edit'),
        ];
    }
}