<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntregaProduccionResource\Pages;
use App\Models\EntregaProduccion;
use App\Models\AsignacionTrabajo;
use App\Models\Tarifa; // Importante para buscar precios
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Helpers para la lógica reactiva
use Filament\Forms\Get;
use Filament\Forms\Set;

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
                            // 1. SELECCIONAR ASIGNACIÓN (Reactivo)
                            Select::make('asignacion_trabajo_id')
                                ->label('Trabajo Asignado')
                                ->options(function () {
                                    // Mostramos solo asignaciones pendientes o en proceso
                                    return AsignacionTrabajo::whereIn('estado', ['pendiente', 'en_proceso'])
                                        ->with(['operario', 'ordenItem.tipoPrenda']) // Eager loading
                                        ->get()
                                        ->mapWithKeys(function ($item) {
                                            return [$item->id => "{$item->operario->primer_nombre} - {$item->ordenItem->tipoPrenda->nombre} ({$item->cantidad_asignada} pzas)"];
                                        });
                                })
                                ->searchable()
                                ->required()
                                ->live() // <--- ACTIVA LA MAGIA
                                ->afterStateUpdated(function ($state, Set $set) {
                                    // Lógica: Buscar la tarifa de la prenda asignada
                                    if (!$state) return;
                                    
                                    $asignacion = AsignacionTrabajo::find($state);
                                    if ($asignacion) {
                                        // Buscamos la prenda ID
                                        $prendaId = $asignacion->ordenItem->tipo_prenda_id;
                                        
                                        // Buscamos tarifa activa
                                        $tarifa = Tarifa::where('tipo_prenda_id', $prendaId)
                                            ->where('estado', true)
                                            ->first();

                                        if ($tarifa) {
                                            $set('tarifa_aplicada', $tarifa->precio_mano_obra);
                                        } else {
                                            // Alerta visual si no hay tarifa (Opcional)
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
                                ->readOnly() // El usuario no debería editar esto, viene de la tabla Tarifas
                                ->required()
                                ->dehydrated(), // Obliga a guardar aunque sea readonly

                            // 3. CANTIDAD (Calcula el total al escribir)
                            TextInput::make('cantidad_entregada')
                                ->label('Cantidad Entregada')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->live(onBlur: true) // Calcula cuando sales del campo
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $tarifa = (float) $get('tarifa_aplicada');
                                    $cantidad = (float) $state;
                                    $set('monto_generado', $cantidad * $tarifa);
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
                    ->label('Recibido')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListEntregaProduccions::route('/'),
            'create' => Pages\CreateEntregaProduccion::route('/create'),
            'edit' => Pages\EditEntregaProduccion::route('/{record}/edit'),
        ];
    }
}