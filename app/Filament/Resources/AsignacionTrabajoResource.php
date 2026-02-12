<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsignacionTrabajoResource\Pages;
use App\Models\AsignacionTrabajo;
use App\Models\OrdenItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class AsignacionTrabajoResource extends Resource
{
    protected static ?string $model = AsignacionTrabajo::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Asignaciones de Trabajo';
    protected static ?string $modelLabel = 'Asignaciones';
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
                                ->relationship(
                                    name: 'ordenItem', 
                                    titleAttribute: 'id',
                                    modifyQueryUsing: fn (Builder $query) => $query
                                        ->with(['tipoPrenda'])
                                        ->whereRaw('cantidad > (SELECT COALESCE(SUM(cantidad_asignada), 0) FROM asignaciones_trabajo WHERE asignaciones_trabajo.orden_item_id = orden_items.id)')
                                ) 
                                ->getOptionLabelFromRecordUsing(function ($record) {
                                    $yaAsignado = \App\Models\AsignacionTrabajo::where('orden_item_id', $record->id)->sum('cantidad_asignada');
                                    $restante = $record->cantidad - $yaAsignado;
                                    $prenda = $record->tipoPrenda ? $record->tipoPrenda->nombre : 'Prenda';
                                    return "Orden #{$record->orden_produccion_id} - {$prenda} (Faltan: {$restante} pzas)";
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live() 
                                ->afterStateUpdated(fn (callable $set) => $set('cantidad_asignada', null))
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
                                ->minValue(1)
                                ->maxValue(function (Get $get) {
                                    $ordenItemId = $get('orden_item_id');
                                    if (!$ordenItemId) return 9999;
                                    $item = OrdenItem::find($ordenItemId);
                                    if (!$item) return 9999;
                                    $yaAsignado = AsignacionTrabajo::where('orden_item_id', $ordenItemId)->sum('cantidad_asignada');
                                    return max(0, $item->cantidad - $yaAsignado);
                                })
                                ->helperText(function (Get $get) {
                                    $ordenItemId = $get('orden_item_id');
                                    if (!$ordenItemId) return 'Selecciona un item primero.';
                                    $item = OrdenItem::find($ordenItemId);
                                    $yaAsignado = AsignacionTrabajo::where('orden_item_id', $ordenItemId)->sum('cantidad_asignada');
                                    $restante = max(0, $item->cantidad - $yaAsignado);
                                    return "Quedan {$restante} prendas disponibles por asignar.";
                                }),
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
            // ESTO EVITA QUE AL CLICKEAR SE ABRA EL EDITAR
            ->recordUrl(null)
            
            ->columns([
                TextColumn::make('ordenItem.orden_produccion_id')
                    ->label('Orden #')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('operario.primer_nombre') 
                    ->label('Operario')
                    ->sortable()
                    ->searchable(['primer_nombre', 'apellido_paterno']) 
                    ->formatStateUsing(fn ($record) => "{$record->operario->primer_nombre} {$record->operario->apellido_paterno}"),

                TextColumn::make('ordenItem.tipoPrenda.nombre')
                    ->label('Prenda'),

                TextColumn::make('cantidad_asignada')
                    ->label('Cant.')
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('fecha_estimada_entrega')
                    ->label('Entrega')
                    ->date('d/m/y')
                    ->sortable(),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',   // Amarillo
                        'en_proceso' => 'info',     // Azul
                        'completada' => 'success',  // Verde
                        'cancelada' => 'danger',    // Rojo
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('createdBy.name')
                    ->label('Creado Por')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime()
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
                EditAction::make()
                    ->visible(fn (AsignacionTrabajo $record) => $record->estado === 'pendiente')
                    ->tooltip('Solo editable si está pendiente'),

                DeleteAction::make()
                    ->label('Anular')
                    ->modalHeading('¿Anular Asignación?')
                    ->modalDescription('El cupo de estas prendas se liberará y la Orden se actualizará.')
                    ->visible(fn (AsignacionTrabajo $record) => $record->estado === 'pendiente')
                    ->action(function (AsignacionTrabajo $record) {
                        try {
                            DB::statement('CALL SP_ANULAR_ASIGNACION(?)', [$record->id]);
                            Notification::make()->title('Anulada')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsignacionTrabajos::route('/'),
            'create' => Pages\CreateAsignacionTrabajo::route('/create'),
            'edit' => Pages\EditAsignacionTrabajo::route('/{record}/edit'),
        ];
    }
}