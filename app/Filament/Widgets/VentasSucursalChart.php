<?php

namespace App\Filament\Widgets;

use App\Models\Venta;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VentasSucursalChart extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Ventas';
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'half'; // Ocupa la mitad de la fila

    protected function getData(): array
    {
        $user = auth()->user();

        // LÓGICA PARA SUPER ADMIN: Comparativo de todas las sucursales
        if ($user->hasRole('super_admin')) {
            $data = Venta::query()
                ->join('locales', 'ventas.local_id', '=', 'locales.id')
                ->selectRaw('locales.nombre as sucursal, SUM(total) as total')
                ->where('ventas.estado_pago', 'pagado')
                ->groupBy('sucursal')
                ->get();

            return [
                'datasets' => [[
                    'label' => 'Ventas por Local',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => ['#fbbf24', '#10b981', '#3b82f6', '#f43f5e'],
                ]],
                'labels' => $data->pluck('sucursal')->toArray(),
            ];
        }

        // LÓGICA PARA VENDEDOR: Su rendimiento de los últimos 7 días
        $ventasSemanales = Venta::query()
            ->where('local_id', $user->local_id)
            ->where('estado_pago', 'pagado')
            ->where('fecha_emision', '>=', now()->subDays(7))
            ->selectRaw('DATE(fecha_emision) as fecha, SUM(total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Mis Ventas (Últimos 7 días)',
                'data' => $ventasSemanales->pluck('total')->toArray(),
                'backgroundColor' => '#10b981',
            ]],
            'labels' => $ventasSemanales->pluck('fecha')->map(fn($f) => date('d/m', strtotime($f)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return auth()->user()->hasRole('super_admin') ? 'doughnut' : 'bar';
    }
}