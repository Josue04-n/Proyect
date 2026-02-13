<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaResource\Pages;
use App\Models\Cuenta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// --- IMPORTACIONES DE FORMULARIO ---
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

// --- IMPORTACIONES DE TABLA ---
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class CuentaResource extends Resource
{
    protected static ?string $model = Cuenta::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes'; // Icono de billetes

    protected static ?string $navigationLabel = 'Cuentas';

    protected static ?string $modelLabel = 'Cuenta';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Cuenta')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('operario_id')
                                ->label('Operario Propietario')
                                ->relationship('operario', 'primer_nombre') 
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->primer_nombre} {$record->apellido_paterno} - {$record->cedula}") // Formato personalizado
                                ->searchable() 
                                ->preload() 
                                ->required(),

                            TextInput::make('num_cuenta')
                                ->label('Número de Cuenta')
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            TextInput::make('saldo')
                                ->label('Saldo Inicial')
                                ->numeric()
                                ->prefix('$')
                                ->default(0.00),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('operario.nombre_completo')
                    ->label('Operario')
                    ->searchable(['primer_nombre', 'apellido_paterno'])
                    ->sortable(),

                TextColumn::make('num_cuenta')
                    ->label('No. Cuenta')
                    ->searchable(),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money('USD') 
                    ->sortable()
                    ->color(fn (string $state): string => $state < 0 ? 'danger' : 'success'), // Rojo si es negativo (deuda)

                TextColumn::make('createdBy.name') 
                    ->label('Creado Por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // 
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentas::route('/'),
            'create' => Pages\CreateCuenta::route('/create'),
            'edit' => Pages\EditCuenta::route('/{record}/edit'),
        ];
    }
}