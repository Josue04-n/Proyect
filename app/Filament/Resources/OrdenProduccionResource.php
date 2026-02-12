<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenProduccionResource\Pages;
use App\Models\OrdenProduccion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Componentes
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater; 
use Filament\Tables\Columns\SelectColumn; 

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Tables\Actions\ViewAction;

// Columnas
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class OrdenProduccionResource extends Resource
{
    protected static ?string $model = OrdenProduccion::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Órdenes de Producción';

    protected static ?string $modelLabel = 'Orden de Produccion';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECCIÓN 1: DATOS DE CABECERA (CLIENTE Y FECHAS)
                Section::make('Datos Generales del Pedido')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('cliente_id')
                                ->label('Cliente')
                                ->relationship('cliente', 'identificacion')
                                ->getOptionLabelFromRecordUsing(fn ($record) => 
                                    $record->tipo_cliente === 'juridica'
                                        ? "{$record->razon_social}"
                                        : "{$record->primer_nombre} {$record->apellido_paterno}"
                                )
                                ->searchable(['primer_nombre', 'apellido_paterno', 'razon_social'])
                                ->preload()
                                ->required(),

                            Select::make('contrato_id')
                                ->label('Contrato')
                                ->relationship('contrato', 'codigo_contrato')
                                ->searchable()
                                ->nullable(),
                        ]),

                        Grid::make(3)->schema([
                            DatePicker::make('fecha_recepcion')
                                ->label('Recepción')
                                ->default(now())
                                ->required(),

                            DatePicker::make('fecha_entrega_estimada')
                                ->label('Entrega Estimada')
                                ->required()
                                ->afterOrEqual('fecha_recepcion'),

                            Select::make('estado')
                                ->options([
                                    'pendiente' => 'Pendiente',
                                    'parcial' => 'Parcial',
                                    'en_proceso' => 'En Proceso',
                                    'finalizada' => 'Finalizada',
                                    'cerrada' => 'Cerrada',   
                                ])
                                ->default('pendiente')
                                ->required()
                                ->native(false),
                        ]),
                    ]),

                // SECCIÓN 2: DETALLES DE PRENDAS (REPEATER)
                Section::make('Lista de Prendas a Confeccionar')
                    ->schema([
                        Repeater::make('items') // <--- Nombre de la relación hasMany
                            ->relationship()
                            ->schema([
                                Grid::make(4)->schema([
                                    Select::make('tipo_prenda_id')
                                        ->label('Prenda')
                                        ->relationship('tipoPrenda', 'nombre')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(1),

                                    TextInput::make('talla')
                                        ->label('Talla')
                                        ->placeholder('Ej: M, 34')
                                        ->required(),

                                    TextInput::make('color')
                                        ->label('Color')
                                        ->placeholder('Ej: Azul'),

                                    TextInput::make('cantidad')
                                        ->label('Cant.')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->required(),
                                ]),
                            ])
                            ->addActionLabel('Agregar otra prenda al pedido')
                            ->columns(1)
                            ->defaultItems(1),
                    ]),

                Section::make('Observaciones')
                    ->collapsed()
                    ->schema([
                        Textarea::make('observacion')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ORDEN 
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(), 

                // CLIENTE 
                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->searchable(['primer_nombre', 'apellido_paterno', 'razon_social']) // <--- BUSCADOR RESTAURADO
                    ->sortable(),

                // TOTAL PRENDAS
                TextColumn::make('items_sum_cantidad')
                    ->sum('items', 'cantidad')
                    ->label('Prendas')
                    ->alignCenter()
                    ->badge()
                    ->toggleable(), 

                //FECHAS
                TextColumn::make('fecha_recepcion')
                    ->label('Recepción')
                    ->date('d/m/y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // <--- Oculto por defecto

                TextColumn::make('fecha_entrega_estimada')
                    ->label('Entrega')
                    ->date('d/m/y')
                    ->sortable()
                    ->color(fn ($record) => $record->fecha_entrega_estimada < now() && $record->estado !== 'entregada' ? 'danger' : null)
                    ->toggleable(),

                // ESTADO EDITABLE
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',  
                        'parcial' => 'info',     
                        'en_proceso' => 'primary',  
                        'finalizada' => 'success',  
                        'cerrada' => 'danger',   
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable()
                    ->toggleable(),

                
                // CREADO POR 
                TextColumn::make('createdBy.name')
                    ->label('Creado Por')
                    ->toggleable(isToggledHiddenByDefault: true), 
            ])
            ->defaultSort('created_at', 'desc')

            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'parcial' => 'Parcial',
                        'finalizada' => 'Finalizada',
                        'cerrada' => 'Cerrada',
                    ]),
            ])

            ->actions([
                ViewAction::make()
                    ->label('Ver Items')
                    ->color('info')
                    ->modalWidth('xl'),
                
                EditAction::make()
                    ->visible(fn (OrdenProduccion $record) => $record->estado === 'pendiente')
                    ->tooltip(fn (OrdenProduccion $record) => $record->estado === 'pendiente' ? 'Eliminar Orden' : 'No se puede eliminar una orden en proceso o finalizada'),
                DeleteAction::make()
                    ->visible(fn (OrdenProduccion $record) => $record->estado === 'pendiente')
                    ->tooltip(fn (OrdenProduccion $record) => $record->estado === 'pendiente' ? 'Eliminar Orden' : 'No se puede eliminar una orden en proceso o finalizada'),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // DATOS GENERALES
                InfoSection::make('Datos del Pedido')
                    ->schema([
                        InfoGrid::make(3)->schema([
                            TextEntry::make('cliente.nombre_completo')->label('Cliente'),
                            TextEntry::make('contrato.codigo_contrato')->label('Contrato'),
                            TextEntry::make('estado')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'pendiente' => 'gray',
                                    'en_proceso' => 'info',
                                    'parcial' => 'warning',
                                    'finalizada' => 'success',
                                    'entregada' => 'success',
                                    'cerrada' => 'danger',
                                    default => 'gray',
                                }),
                        ]),
                        InfoGrid::make(2)->schema([
                            TextEntry::make('fecha_recepcion')->date(),
                            TextEntry::make('fecha_entrega_estimada')->date(),
                        ]),
                        TextEntry::make('observacion')->columnSpanFull(),
                    ]),

                // LISTA DE PRENDAS (Aquí ves los items)
                InfoSection::make('Prendas Solicitadas')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                InfoGrid::make(4)->schema([
                                    TextEntry::make('tipoPrenda.nombre')->label('Prenda'),
                                    TextEntry::make('talla'),
                                    TextEntry::make('color'),
                                    TextEntry::make('cantidad')->badge()->color('info'),
                                ]),
                            ])
                            ->columns(1)
                            ->contained(false),
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
            'index' => Pages\ListOrdenProduccions::route('/'),
            'create' => Pages\CreateOrdenProduccion::route('/create'),
            'edit' => Pages\EditOrdenProduccion::route('/{record}/edit'),
        ];
    }
}