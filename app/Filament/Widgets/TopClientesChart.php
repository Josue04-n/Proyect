<?php

namespace App\Filament\Widgets;

use App\Models\Venta;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopClientesChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Clientes (Mayor Facturación)';
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'half'; 

    protected function getData(): array
    {
        $user = auth()->user();

        // Consulta para obtener los clientes que más han gastado
        $topClientes = Venta::query()
            ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->select(
                DB::raw("CASE 
                    WHEN clientes.tipo_cliente = 'juridica' THEN clientes.razon_social 
                    ELSE CONCAT(clientes.primer_nombre, ' ', clientes.apellido_paterno) 
                END as nombre_cliente"),
                DB::raw('SUM(ventas.total) as total_gastado')
            )
            ->where('ventas.estado_pago', 'pagado')
            // Filtro de sucursal
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                return $query->where('ventas.local_id', $user->local_id);
            })
            ->groupBy('clientes.id', 'nombre_cliente')
            ->orderByDesc('total_gastado')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Comprado ($)',
                    'data' => $topClientes->pluck('total_gastado')->toArray(),
                    'backgroundColor' => '#3b82f6', // Azul profesional
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $topClientes->pluck('nombre_cliente')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Esto hace que las barras sean horizontales (estilo ranking)
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}