<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Pago;
use App\Models\Prestamo;
use Carbon\Carbon;

class TotalesMensualesChart extends ChartWidget
{
    protected static ?string $heading = '📊 Total Mensual en el Año';
    protected static string $color = 'success';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $año = request()->query('año', Carbon::now()->year);

        $meses = range(1, 12);
        $totalesCobrado = [];
        $totalesPrestado = [];

        foreach ($meses as $mes) {
            $inicioMes = Carbon::create($año, $mes, 1)->startOfMonth();
            $finMes = Carbon::create($año, $mes, 1)->endOfMonth();

            $totalesCobrado[] = Pago::whereBetween('fecha_pago', [$inicioMes, $finMes])->sum('monto');
            $totalesPrestado[] = Prestamo::whereBetween('fecha_prestamo', [$inicioMes, $finMes])->sum('monto');
        }

        return [
            'labels' => [
                'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
            ],
            'datasets' => [
                [
                    'label' => '💰 Total Cobrado',
                    'data' => $totalesCobrado,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'fill' => false,
                ],
                [
                    'label' => '💵 Total Prestado',
                    'data' => $totalesPrestado,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'fill' => false,
                ],
            ],
        ];
    }
}
