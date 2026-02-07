<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PagoOperarioResource\Pages;
use App\Models\PagoOperario;
use App\Models\Cuenta; // <--- IMPORTANTE: Importar el modelo Cuenta
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

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

use Filament\Forms\Get;
use Filament\Forms\Set;

class PagoOperarioResource extends Resource
{
    protected static ?string $model = PagoOperario::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pagos a Operarios';

    protected static ?string $modelLabel = 'Pago';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalle del Pago')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. SELECCIONAR OPERARIO
                            Select::make('operario_id')
                                ->label('Operario')
                                ->relationship('operario', 'primer_nombre', function($query){
                                    $query->where('is_active', true);
                                })
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->primer_nombre} {$record->apellido_paterno}")
                                ->searchable(['primer_nombre', 'apellido_paterno'])
                                ->preload()
                                ->required()
                                ->live() // Detecta el cambio
                                ->afterStateUpdated(function ($state, Set $set) {
                                    // Si no hay operario seleccionado, saldo es 0
                                    if (! $state) {
                                        $set('saldo_actual', 0);
                                        return;
                                    }

                                    // --- CONSULTA DIRECTA A LA TABLA CUENTAS ---
                                    // Buscamos la cuenta asociada a este operario
                                    $cuenta = Cuenta::where('operario_id', $state)->first();

                                    // Obtenemos el saldo (si no tiene cuenta, es 0)
                                    $saldo = $cuenta ? $cuenta->saldo : 0;

                                    // Asignamos el valor al campo visual
                                    $set('saldo_actual', number_format($saldo, 2, '.', ''));
                                }),

                            // 2. SALDO VISUAL (Lectura desde la tabla 'cuentas')
                            TextInput::make('saldo_actual')
                                ->label('Saldo Disponible en Cuenta')
                                ->prefix('$')
                                
                                // EL OJITO PARA OCULTAR/VER
                                ->password()
                                ->revealable()
                                
                                ->readOnly()
                                ->dehydrated(false) // No guardar en la tabla de pagos
                                ->helperText('Saldo actual extraído de la cuenta del operario.'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('monto')
                                ->label('Monto a Pagar')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->maxValue(function (Get $get) {
                                    return (float) $get('saldo_actual');
                                }),

                            DatePicker::make('fecha_pago')
                                ->label('Fecha')
                                ->default(now())
                                ->required()
                                ->disabled()    
                                ->dehydrated(), 
                        ]),

                        Select::make('forma_pago')
                            ->options([
                                'Efectivo' => 'Efectivo',
                                'Transferencia' => 'Transferencia',
                                'Cheque' => 'Cheque',
                            ])
                            ->default('Efectivo')
                            ->required(),

                        Textarea::make('observacion')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('operario.primer_nombre')
                    ->label('Operario')
                    ->sortable()
                    ->searchable(['primer_nombre', 'apellido_paterno'])
                    ->formatStateUsing(fn ($record) => "{$record->operario->primer_nombre} {$record->operario->apellido_paterno}")
                    ->weight('bold'),

                TextColumn::make('monto')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('forma_pago')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Efectivo' => 'success',
                        'Transferencia' => 'info',
                        'Cheque' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('fecha_pago')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('usuarioPaga.name')
                    ->label('Pagado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha_pago', 'desc')
            ->filters([
                SelectFilter::make('operario_id')
                    ->label('Filtrar por Operario')
                    ->relationship('operario', 'primer_nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->primer_nombre} {$record->apellido_paterno}"),

                SelectFilter::make('forma_pago')
                    ->options([
                        'Efectivo' => 'Efectivo',
                        'Transferencia' => 'Transferencia',
                    ]),
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
            'index' => Pages\ListPagoOperarios::route('/'),
            'create' => Pages\CreatePagoOperario::route('/create'),
            'edit' => Pages\EditPagoOperario::route('/{record}/edit'),
        ];
    }
}