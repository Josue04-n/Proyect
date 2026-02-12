<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntregaProduccionResource\Pages;
use App\Models\EntregaProduccion;
use App\Models\AsignacionTrabajo;
use App\Models\Tarifa; // IMPORTANTE: Restaurado para buscar precios
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Helpers
use Filament\Forms\Get;
use Filament\Forms\Set;

// Componentes Visuales
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class EntregaProduccionResource extends Resource
{
    protected static ?string $model = EntregaProduccion::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Recibir Trabajo (Entregas)';

    protected static ?string $modelLabel = 'Entrega de Producción';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Registro de Entrega')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. SELECCIONAR ASIGNACIÓN (Reactivo y Filtrado Inteligente)
                            Select::make('asignacion_trabajo_id')
                                ->label('Trabajo Asignado')
                                ->options(function () {
                                    return AsignacionTrabajo::whereIn('estado', ['pendiente', 'en_proceso'])
                                        ->with(['operario', 'ordenItem.tipoPrenda'])
                                        ->get()
                                        ->filter(function ($item) {
                                            // Filtramos para que solo salgan las asignaciones que AÚN TIENEN prendas pendientes de entregar
                                            $entregado = EntregaProduccion::where('asignacion_trabajo_id', $item->id)->sum('cantidad_entregada');
                                            return ($item->cantidad_asignada - $entregado) > 0;
                                        })
                                        ->mapWithKeys(function ($item) {
                                            // Calculamos el restante para mostrarlo en el texto
                                            $entregado = EntregaProduccion::where('asignacion_trabajo_id', $item->id)->sum('cantidad_entregada');
                                            $restante = $item->cantidad_asignada - $entregado;
                                            return [$item->id => "{$item->operario->primer_nombre} - {$item->ordenItem->tipoPrenda->nombre} (Faltan: {$restante} pzas)"];
                                        });
                                })
                                ->searchable()
                                ->required()
                                ->live() // <--- ACTIVA LA MAGIA
                                ->afterStateUpdated(function ($state, Set $set) {
                                    // Limpiamos los campos al cambiar de trabajo
                                    $set('cantidad_entregada', null);
                                    $set('monto_generado', null);

                                    if (!$state) return;
                                    
                                    // --- LOGICA RESTAURADA: Buscar la tarifa de la prenda ---
                                    $asignacion = AsignacionTrabajo::find($state);
                                    if ($asignacion && $asignacion->ordenItem) {
                                        $prendaId = $asignacion->ordenItem->tipo_prenda_id;
                                        
                                        $tarifa = Tarifa::where('tipo_prenda_id', $prendaId)
                                            ->where('estado', true)
                                            ->first();

                                        if ($tarifa) {
                                            $set('tarifa_aplicada', $tarifa->precio_mano_obra);
                                        } else {
                                            $set('tarifa_aplicada', 0);
                                        }
                                    }
                                }),

                            DateTimePicker::make('fecha_recibo_real')
                                ->label('Fecha y Hora de Recibo')
                                ->default(now())
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            // 2. TARIFA (Se llena sola, readonly)
                            TextInput::make('tarifa_aplicada')
                                ->label('Tarifa ($)')
                                ->numeric()
                                ->prefix('$')
                                ->readOnly() // El usuario no la puede editar
                                ->required()
                                ->dehydrated(), // Obliga a guardar

                            // 3. CANTIDAD (Con Validación para no pasarse del límite)
                            TextInput::make('cantidad_entregada')
                                ->label('Cantidad Entregada')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(function (Get $get) {
                                    // Tope máximo dinámico
                                    $id = $get('asignacion_trabajo_id');
                                    if (!$id) return 9999;
                                    
                                    $asignacion = AsignacionTrabajo::find($id);
                                    $yaEntregado = EntregaProduccion::where('asignacion_trabajo_id', $id)->sum('cantidad_entregada');
                                    
                                    return $asignacion->cantidad_asignada - $yaEntregado;
                                })
                                ->helperText(function(Get $get) {
                                    $id = $get('asignacion_trabajo_id');
                                    if (!$id) return '';
                                    
                                    $asignacion = AsignacionTrabajo::find($id);
                                    $yaEntregado = EntregaProduccion::where('asignacion_trabajo_id', $id)->sum('cantidad_entregada');
                                    $restante = $asignacion->cantidad_asignada - $yaEntregado;
                                    
                                    return "Máximo permitido: {$restante} prendas.";
                                })
                                ->live(onBlur: true) 
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $tarifa = (float) $get('tarifa_aplicada');
                                    $cantidad = (float) $state;
                                    $set('monto_generado', round($cantidad * $tarifa, 2));
                                }),

                            // 4. TOTAL (Calculado automático)
                            TextInput::make('monto_generado')
                                ->label('Total a Pagar')
                                ->numeric()
                                ->prefix('$')
                                ->readOnly()
                                ->dehydrated()
                                ->required(),
                        ]),

                        Textarea::make('observacion')
                            ->label('Observaciones (Defectos, arreglos...)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asignacionTrabajo.operario.primer_nombre')
                    ->label('Operario')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('asignacionTrabajo.ordenItem.tipoPrenda.nombre')
                    ->label('Prenda')
                    ->sortable(),

                TextColumn::make('cantidad_entregada')
                    ->label('Cant.')
                    ->alignCenter()
                    ->weight('bold'),

                TextColumn::make('tarifa_aplicada')
                    ->label('Tarifa')
                    ->money('USD')
                    ->color('gray'),

                TextColumn::make('monto_generado')
                    ->label('Generado')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('fecha_recibo_real')
                    ->label('Fecha Recibido')
                    ->dateTime('d/m/y H:i')
                    ->sortable(),

                TextColumn::make('createdBy.name')
                    ->label('Recibido Por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // NOTA: Como conectamos esto a la Base de Datos con el Procedure que mueve plata e inventario,
                // si usas Edit o Delete normales, se va a romper la contabilidad.
                // Lo dejo comentado por seguridad hasta que hagamos el Procedure de reversa.
                
                // EditAction::make(),
                // DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntregaProduccions::route('/'),
            'create' => Pages\CreateEntregaProduccion::route('/create'),
            // 'edit' => Pages\EditEntregaProduccion::route('/{record}/edit'),
        ];
    }
}