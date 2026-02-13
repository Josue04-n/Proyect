<?php

namespace App\Filament\Widgets;

use App\Models\MovimientoCaja;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CashFlowChart extends ChartWidget
{
    protected static ?string $heading = 'Flujo de Caja (Últimos 12 Meses)';
    
    // Ocupa todo el ancho
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Datos de INGRESOS 
        $dataIngresos = Trend::query(MovimientoCaja::where('tipo', 'ingreso'))
            ->between(start: now()->subYear(), end: now())
            ->perMonth()
            ->sum('monto');

        // Datos de EGRESOS 
        $dataEgresos = Trend::query(MovimientoCaja::where('tipo', 'egreso'))
            ->between(start: now()->subYear(), end: now())
            ->perMonth()
            ->sum('monto');

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $dataIngresos->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#22c55e', // Verde
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Gastos',
                    'data' => $dataEgresos->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#ef4444', // Rojo
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $dataIngresos->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Puede ser 'bar', 'line', 'pie'
    }
}