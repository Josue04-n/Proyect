<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompraResource\Pages;
use App\Models\Compra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Componentes de Formulario
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater; // El componente clave para el detalle
use Filament\Forms\Get;
use Filament\Forms\Set;

// Componentes de Tabla
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class CompraResource extends Resource
{
    protected static ?string $model = Compra::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Compras / Gastos';

    protected static ?string $modelLabel = 'Compra';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECCIÓN 1: DATOS DE LA CABECERA ---
                Section::make('Datos de la Factura')
                    ->schema([
                        Grid::make(3)->schema([
                            // Proveedor
                            Select::make('proveedor_id')
                                ->label('Proveedor')
                                ->relationship('proveedor', 'razon_social') // Asegúrate que sea 'razon_social' o 'nombre'
                                ->searchable()
                                ->preload()
                                ->required(),

                            // Número de Factura
                            TextInput::make('numero_comprobante')
                                ->label('N° Comprobante')
                                ->required()
                                ->maxLength(255),

                            // Fecha
                            DatePicker::make('fecha_compra')
                                ->label('Fecha de Emisión')
                                ->default(now())
                                ->required(),
                        ]),

                        // Estado de la Compra
                        Select::make('estado')
                            ->options([
                                'pendiente' => 'Pendiente (Borrador)',
                                'recibida' => 'Recibida (Ingresa Stock)',
                                'anulada' => 'Anulada',
                            ])
                            ->default('pendiente')
                            ->required()
                            ->reactive(), // Puede servir para ocultar cosas según estado
                    ]),

                // --- SECCIÓN 2: DETALLES (PRODUCTOS) ---
                Section::make('Detalle de Compra')
                    ->schema([
                        Repeater::make('detalles') // IMPORTANTE: Debe coincidir con la relación en el Modelo Compra
                            ->relationship()
                            ->schema([
                                Grid::make(4)->schema([
                                    
                                    // 1. Insumo
                                    Select::make('insumo_id')
                                        ->label('Insumo / Material')
                                        ->relationship('insumo', 'nombre')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan(2), // Ocupa 2 columnas para verse mejor

                                    // 2. Cantidad
                                    TextInput::make('cantidad')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->minValue(0.01)
                                        ->live(onBlur: true) // Se activa al salir del campo
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::calcularSubtotal($get, $set);
                                        }),

                                    // 3. Precio Unitario
                                    TextInput::make('precio_unitario')
                                        ->label('Costo Unit.')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required()
                                        ->minValue(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::calcularSubtotal($get, $set);
                                        }),

                                    // 4. Subtotal (Calculado)
                                    TextInput::make('subtotal')
                                        ->numeric()
                                        ->prefix('$')
                                        ->readOnly()
                                        ->dehydrated(), // Obliga a guardar el dato en BD aunque sea readonly
                                ]),
                            ])
                            ->live() // Escucha cambios en agregar/eliminar filas
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularTotalGeneral($get, $set);
                            })
                            ->deleteAction(
                                fn ($action) => $action->after(fn (Get $get, Set $set) => self::calcularTotalGeneral($get, $set)),
                            )
                            ->columnSpanFull()
                            ->defaultItems(1), // Inicia con 1 fila vacía
                    ]),

                // --- SECCIÓN 3: TOTALES ---
                Section::make()
                    ->schema([
                        Grid::make(2)->schema([
                            Textarea::make('observacion')
                                ->label('Observaciones')
                                ->rows(2),

                            TextInput::make('total')
                                ->label('TOTAL FACTURA')
                                ->numeric()
                                ->prefix('$')
                                ->readOnly()
                                ->dehydrated()
                                ->extraInputAttributes([
                                    'style' => 'font-size: 1.5rem; font-weight: bold; color: #16a34a; text-align: right;'
                                ]),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('proveedor.razon_social')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('numero_comprobante')
                    ->label('N° Factura')
                    ->searchable(),

                TextColumn::make('fecha_compra')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total')
                    ->money('USD')
                    ->alignRight()
                    ->color('danger'),


                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'recibida' => 'success',
                        'anulada' => 'danger',
                    }),

                TextColumn::make('observacion')
                    ->label('Observaciones')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('createdBy.name')
                    ->label('Creado Por')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updatedBy.name')
                    ->label('Actualizado Por')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('fecha_compra', 'desc')
            ->filters([
                SelectFilter::make('proveedor')
                    ->relationship('proveedor', 'razon_social'),
                
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'recibida' => 'Recibida',
                        'anulada' => 'Anulada',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCompras::route('/'),
            'create' => Pages\CreateCompra::route('/create'),
            'edit' => Pages\EditCompra::route('/{record}/edit'),
        ];
    }

    // --- LOGICA DE CÁLCULO ---

    /**
     * Calcula el subtotal de una fila (Cantidad * Precio)
     * Y luego llama al cálculo general.
     */
    public static function calcularSubtotal(Get $get, Set $set): void
    {
        $cantidad = (float) $get('cantidad');
        $precio = (float) $get('precio_unitario');
        
        $subtotal = round($cantidad * $precio, 2);
        
        $set('subtotal', $subtotal);

        // Al cambiar una línea, actualizamos el total general inmediatamente
        self::calcularTotalGeneral($get, $set);
    }

    /**
     * Suma todos los subtotales del Repeater para obtener el Total Final
     */
    public static function calcularTotalGeneral(Get $get, Set $set): void
    {
        $items = $get('detalles'); 
        $sumaTotal = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                $sumaTotal += (float) ($item['subtotal'] ?? 0);
            }
        }

        $set('total', round($sumaTotal, 2));
    }
}