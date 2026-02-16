<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaResource\Pages;
use App\Models\Venta;
use App\Models\PrendaTienda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Componentes Visuales
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Get;
use Filament\Forms\Set;

// Componentes de Tabla
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

use Illuminate\Database\Eloquent\Builder;

class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Facturación / Caja';
    protected static ?string $modelLabel = 'Venta';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECCIÓN 1: CABECERA ---
                Section::make('Información de la Venta')
                    ->schema([
                        Grid::make(3)->schema([
                            // 1. Cliente
                            Select::make('cliente_id')
                                ->label('Cliente')
                                ->relationship('cliente', 'identificacion')
                                ->getOptionLabelFromRecordUsing(fn ($record) => 
                                    $record->tipo_cliente === 'juridica'
                                        ? $record->razon_social
                                        : "{$record->primer_nombre} {$record->apellido_paterno}"
                                )
                                ->searchable(['primer_nombre', 'apellido_paterno', 'razon_social', 'identificacion'])
                                ->preload()
                                ->required()
                                ->columnSpan(1),

                            // 2. Fecha (BLOQUEADA Y OBLIGATORIA)
                            DateTimePicker::make('fecha_emision')
                                ->label('Fecha Emisión')
                                ->default(now())
                                ->required()
                                ->disabled()    
                                ->dehydrated(), 

                            // 3. SRI (Checkbox Limpio)
                            Checkbox::make('requiere_factura')
                                ->label('Generar Factura SRI')
                                ->default(false)
                                ->inline(false),
                        ]),
                        
                        Grid::make(2)->schema([
                            Select::make('metodo_pago')
                                ->label('Forma de Pago')
                                ->options([
                                    'efectivo' => 'Efectivo', 
                                    'tarjeta' => 'Tarjeta', 
                                    'transferencia' => 'Transferencia'
                                ])
                                ->default('efectivo')
                                ->required(),

                            Select::make('estado_pago')
                                ->label('Estado')
                                ->options([
                                    'pagado' => 'Pagado', 
                                    'pendiente' => 'Pendiente', 
                                    'anulado' => 'Anulado'
                                ])
                                ->default('pagado')
                                ->required(),
                        ]),
                    ])
                    ->compact(),

                // --- SECCIÓN 2: DETALLE (GRID 12 COLUMNAS) ---
                Section::make('Productos')
                    ->schema([
                        Repeater::make('detalles')
                            ->hiddenLabel()
                            // REMOVIDO: ->relationship() para que los datos lleguen a $data['detalles']
                            // El SP se encarga de guardar los detalles
                            ->schema([
                                Grid::make(12)->schema([
                                    
                                    // A. PRODUCTO (5 Columnas - Diseño Limpio)
                                                                        Select::make('prenda_tienda_id')
                                        ->label('Producto')
                                        ->options(function () {
                                            $user = auth()->user();

                                            return PrendaTienda::query()
                                                ->with('tipoPrenda')
                                                // Filtro por el local del usuario logueado
                                                ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                                                    return $query->where('local_id', $user->local_id);
                                                })
                                                ->get()
                                                ->mapWithKeys(function ($item) {
                                                    // Opcional: puedes añadir el stock disponible para que el vendedor esté informado
                                                    return [$item->id => "{$item->tipoPrenda->nombre} - {$item->talla} {$item->color} (Stock: {$item->stock_actual})"];
                                                });
                                        })
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(5)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            if ($state) {
                                                $producto = PrendaTienda::find($state);
                                                if ($producto) {
                                                    $set('precio_unitario', $producto->precio_venta);
                                                    $set('tipo_prenda_id', $producto->tipo_prenda_id);
                                                    self::calcularSubtotalFila($get, $set);
                                                }
                                            }
                                        }),

                                    TextInput::make('tipo_prenda_id')->hidden(),

                                    // B. CANTIDAD (2 Columnas)
                                    TextInput::make('cantidad')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->required()
                                        ->columnSpan(2)
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calcularSubtotalFila($get, $set)),

                                    // C. PRECIO (2 Columnas)
                                    TextInput::make('precio_unitario')
                                        ->label('Precio')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required()
                                        ->columnSpan(2)
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calcularSubtotalFila($get, $set)),

                                    // D. TOTAL FILA (3 Columnas)
                                    TextInput::make('subtotal')
                                        ->label('Total')
                                        ->numeric()
                                        ->prefix('$')
                                        ->readOnly()
                                        ->dehydrated()
                                        ->columnSpan(3),
                                ]),
                            ])
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::actualizarTotalesGenerales($get, $set, true))
                            ->deleteAction(fn ($action) => $action->after(fn (Get $get, Set $set) => self::actualizarTotalesGenerales($get, $set, true)))
                            ->columnSpanFull()
                            ->addActionLabel('Agregar Item'),
                    ]),

                // --- SECCIÓN 3: TOTALES ---
                Section::make()
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->prefix('$')
                                ->readOnly()
                                ->dehydrated(),

                            TextInput::make('descuento')
                                ->label('Descuento ($)')
                                ->numeric()
                                ->default(0)
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::actualizarTotalesGenerales($get, $set, false)),

                            TextInput::make('impuestos')
                                ->label('IVA (15%)')
                                ->prefix('$')
                                ->readOnly()
                                ->dehydrated(),

                            
                            TextInput::make('total')
                                ->label('TOTAL')
                                ->prefix('$')
                                ->readOnly()
                                ->dehydrated()
                                ->extraInputAttributes([
                                    'style' => 'font-weight: bold; font-size: 1.5rem; color: #16a34a; text-align: right;'
                                ]),
                        ]),
                    ]),
            ]);
    }

    public static function calcularSubtotalFila(Get $get, Set $set): void
    {
        $cantidad = (float) $get('cantidad');
        $precio = (float) $get('precio_unitario');
        
        $subtotalFila = round($cantidad * $precio, 2);
        $set('subtotal', number_format($subtotalFila, 2, '.', ''));
        
        self::actualizarTotalesGenerales($get, $set, true);
    }

    public static function actualizarTotalesGenerales(Get $get, Set $set, bool $desdeFilaRepeater = false): void
    {
        $pathItems = $desdeFilaRepeater ? '../../detalles' : 'detalles';
        $items = $get($pathItems) ?? [];

        $sumaAcumulada = 0;

        foreach ($items as $item) {
            $c = (float) ($item['cantidad'] ?? 0);
            $p = (float) ($item['precio_unitario'] ?? 0);
            $sumaAcumulada += ($c * $p);
        }

        $pathDesc = $desdeFilaRepeater ? '../../descuento' : 'descuento';
        $descuento = (float) ($get($pathDesc) ?? 0);

        $baseImponible = max(0, $sumaAcumulada - $descuento);
        $iva = round($baseImponible * 0.15, 2);
        $totalFinal = $baseImponible + $iva;

        $prefijo = $desdeFilaRepeater ? '../../' : '';

        $set($prefijo . 'subtotal', number_format($sumaAcumulada, 2, '.', ''));
        $set($prefijo . 'impuestos', number_format($iva, 2, '.', ''));
        $set($prefijo . 'total', number_format($totalFinal, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable()->searchable(),
                
                TextColumn::make('cliente.id')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record) => 
                        $record->cliente->tipo_cliente === 'juridica' 
                        ? $record->cliente->razon_social 
                        : "{$record->cliente->primer_nombre} {$record->cliente->apellido_paterno}"
                    )
                    ->searchable(),

                IconColumn::make('requiere_factura')
                    ->label('SRI')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('fecha_emision')->date('d/m/Y'),
                
                TextColumn::make('total')
                    ->money('USD')
                    ->weight('bold')
                    ->alignRight(),

                TextColumn::make('estado_pago')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pagado' => 'success',
                        'pendiente' => 'warning',
                        'anulado' => 'danger',
                    }),
                
                
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make()->color('info'),

                EditAction::make()
                    ->disabled(fn (Venta $record) => $record->estado_pago === 'pagado')
                    ->icon(fn (Venta $record) => $record->estado_pago === 'pagado' ? 'heroicon-o-lock-closed' : 'heroicon-o-pencil')
                    ->tooltip(fn (Venta $record) => $record->estado_pago === 'pagado' ? 'Venta Cobrada (No editable)' : 'Editar'),

                DeleteAction::make()
                    ->visible(fn (Venta $record) => $record->estado_pago !== 'pagado'),
            ]);

    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array {
        return [
            'index' => Pages\ListVentas::route('/'),
            'create' => Pages\CreateVenta::route('/create'),
            'edit' => Pages\EditVenta::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    if (!$user->hasRole('super_admin')) {
        return $query->where('local_id', $user->local_id);
    }

    return $query;
}
    
}