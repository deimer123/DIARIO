<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Pago;
use App\Models\Prestamo;
use Carbon\Carbon;

class TotalesDiariosChart extends ChartWidget
{
    protected static ?string $heading = '📊 Total Diario en el Mes';
    protected static string $color = 'primary';

    // ✅ Cambia a gráfico de barras para mejor visualización
    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $mes = request()->query('mes', Carbon::now()->month);
        $año = request()->query('año', Carbon::now()->year);
        $diasEnMes = Carbon::create($año, $mes)->daysInMonth;

        // 📌 Crear un array con todos los días del mes inicializados en 0
        $totalesCobrado = array_fill(1, $diasEnMes, 0);
        $totalesPrestado = array_fill(1, $diasEnMes, 0);

        // 📌 Obtener los pagos del mes
        $pagos = Pago::whereYear('fecha_pago', $año)
            ->whereMonth('fecha_pago', $mes)
            ->selectRaw('DAY(fecha_pago) as dia, SUM(monto) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia')
            ->toArray();

        // 📌 Obtener los préstamos del mes
        $prestamos = Prestamo::whereYear('fecha_prestamo', $año)
            ->whereMonth('fecha_prestamo', $mes)
            ->selectRaw('DAY(fecha_prestamo) as dia, SUM(monto) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia')
            ->toArray();

        // 📌 Llenar los datos en los arreglos
        foreach ($pagos as $dia => $total) {
            $totalesCobrado[$dia] = $total;
        }

        foreach ($prestamos as $dia => $total) {
            $totalesPrestado[$dia] = $total;
        }

        return [
            'labels' => array_map(fn ($dia) => (string) $dia, range(1, $diasEnMes)), // 📅 Mostrar todos los días
            'datasets' => [
                [
                    'label' => '💰 Total Cobrado',
                    'data' => array_values($totalesCobrado),
                    'backgroundColor' => 'rgba(8, 149, 244, 0.8)',
                    'borderColor' => 'rgba(8, 149, 244, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => '💵 Total Prestado',
                    'data' => array_values($totalesPrestado),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}
