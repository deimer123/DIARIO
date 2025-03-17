<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Pago;
use App\Models\Prestamo;
use Carbon\Carbon;
use Filament\Facades\Filament;

class EstadisticasDashboard extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('📈 Total Ganancias del Mes', '$' . number_format(Pago::whereMonth('created_at', Carbon::now()->month)->sum('monto'), 2))
                ->description('Ingresos de este mes')
                ->color('success'),

            Card::make('🏦 Total Prestado', '$' . number_format(Prestamo::sum('monto'), 2))
                ->description('Total de dinero prestado')
                ->color('info'),

            Card::make('💳 Dinero Pendiente', '$' . number_format(Prestamo::where('estado', 'pendiente')->sum('monto'), 2))
                ->description('Aún no cobrado')
                ->color('warning'),

            Card::make('🚨 Clientes en Mora', Prestamo::where('estado', 'mora')->count())
                ->description('Clientes con pagos vencidos')
                ->color('danger'),
        ];
    }

    // 🔥 **Nuevo método para restringir a Administradores**
    public static function canView(): bool
    {
        return auth()->user()->hasRole('Administrador'); 
    }
}
