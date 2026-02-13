<?php

namespace App\Filament\Widgets;

use App\Models\MovimientoCaja;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    // Esto hace que el widget se actualice cada 15 segundos automáticamente
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // 1. CALCULAR SALDO TOTAL (Histórico)
        // Sumamos todo lo que entró y restamos todo lo que salió desde el inicio de los tiempos
        $ingresosTotales = MovimientoCaja::where('tipo', 'ingreso')->sum('monto');
        $egresosTotales  = MovimientoCaja::where('tipo', 'egreso')->sum('monto');
        $saldoActual     = $ingresosTotales - $egresosTotales;

        // 2. CALCULAR MOVIMIENTOS DE ESTE MES (Para control mensual)
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes    = Carbon::now()->endOfMonth();

        $ingresosMes = MovimientoCaja::where('tipo', 'ingreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $egresosMes = MovimientoCaja::where('tipo', 'egreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        return [
            // TARJETA 1: SALDO EN CAJA (La más importante)
            Stat::make('Saldo en Caja', '$ ' . number_format($saldoActual, 2))
                ->description('Dinero disponible actualmente')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($saldoActual >= 0 ? 'success' : 'danger') // Verde si es positivo, Rojo si debes
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Gráfico decorativo pequeño

            // TARJETA 2: VENTAS DEL MES
            Stat::make('Ingresos (Este Mes)', '$ ' . number_format($ingresosMes, 2))
                ->description('Ventas acumuladas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            // TARJETA 3: GASTOS DEL MES
            Stat::make('Gastos (Este Mes)', '$ ' . number_format($egresosMes, 2))
                ->description('Pagos a operarios y compras')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}