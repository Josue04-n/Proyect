<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodoContableResource\Pages;
use App\Filament\Resources\PeriodoContableResource\RelationManagers;
use App\Models\PeriodoContable;


use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

//Importaciones para las tablas
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter; 
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;   

//Impotaciones para los formularios
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class PeriodoContableResource extends Resource
{
    protected static ?string $model = PeriodoContable::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Periodos Contables';

    protected static ?string $modelLabel = 'Periodo Contable';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Periodo Contable')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nombre')
                                ->label('Nombre del Periodo Contable')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),

                            DatePicker::make('fecha_inicio')
                                ->label('Fecha de Inicio')
                                ->required()
                                ->maxDate(now()->addYear()),

                            DatePicker::make('fecha_fin')
                                ->label('Fecha de Fin')
                                ->required()
                                ->maxDate(now()->addYear()),

                            Select::make('estado')
                                ->label('Estado del Periodo')
                                ->options([
                                    'abierto' => 'Abierto',
                                    'cerrado' => 'Cerrado',
                                ])
                                ->default('abierto')
                                ->required()
                                ->native(false),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre Periodo')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('fecha_inicio')
                    ->label('Incio')
                    ->date('d/m/y')
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/y')
                    ->sortable(),

                TextColumn::make('estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state):string =>match($state) {
                        'abierto' => 'Abierto',
                        'cerrado' => 'Cerrado',
                        default => $state,
                    })
                    ->color(fn(string $state):string =>match($state){
                        'abierto' => 'success',
                        'cerrado' => 'danger',
                        default => 'gray',
                    }) 
                    ->icon(fn(string $state):string =>match($state){
                        'abierto' => 'heroicon-m-lock-open',
                        'cerrado' => 'heroicon-m-lock-closed',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                    TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
                SelectFilter::make('estado')
                    ->label('Filtrar por Estado')
                    ->options([
                        'abierto' => 'Abierto',
                        'cerrado' => 'Cerrado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPeriodoContables::route('/'),
            'create' => Pages\CreatePeriodoContable::route('/create'),
            'edit' => Pages\EditPeriodoContable::route('/{record}/edit'),
        ];
    }
}
