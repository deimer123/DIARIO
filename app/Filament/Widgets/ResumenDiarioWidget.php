<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Pago;
use App\Models\Prestamo;
use Carbon\Carbon;

class ResumenDiarioWidget extends BaseWidget
{
    protected static ?int $sort = 1; // Orden en el Dashboard
    protected ?string $heading = '📊 Resumen Diario';

    protected function getCards(): array
    {
        return [
            Card::make('📆 Total Cobrado Hoy', '$' . number_format(
                Pago::whereDate('fecha_pago', Carbon::today())->sum('monto'), 
                2
            ))
            ->description('Total de pagos recibidos hoy')
            ->color('info'),

            Card::make('📆 Total Prestado Hoy', '$' . number_format(
                Prestamo::whereDate('fecha_prestamo', Carbon::today())->sum('monto'), 
                2
            ))
            ->description('Total prestado hoy')
            ->color('primary'),
        ];
    }
}
