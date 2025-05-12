<?php

namespace App\Filament\Resources\BaseFinancieraResource\Widgets;

use App\Models\Prestamo;
use App\Models\Pago;
use App\Models\MovimientoFinanciero;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;

class BaseFinancieraOverview extends StatsOverviewWidget
{
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    protected static ?string $pollingInterval = null;
    protected static bool $isLazy = false;

    protected $listeners = ['actualizarEstadisticas'];

    public function mount(): void
    {
        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin = now()->endOfMonth()->toDateString();
    }

    public function actualizarEstadisticas($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    protected function getStats(): array
    {
        $inicio = $this->fechaInicio
            ? Carbon::parse($this->fechaInicio)->startOfDay()
            : now()->startOfMonth();

        $fin = $this->fechaFin
            ? Carbon::parse($this->fechaFin)->endOfDay()
            : now()->endOfMonth();

        // 📌 Consultas filtradas por fecha
        $totalPrestado = Prestamo::whereBetween('fecha_prestamo', [$inicio, $fin])->sum('monto');
        $totalCobrado = Pago::whereBetween('fecha_pago', [$inicio, $fin])->sum('monto');
        $gananciaEstimada = Prestamo::whereBetween('fecha_prestamo', [$inicio, $fin])
            ->get()
            ->sum(fn($p) => $p->monto * ($p->interes / 100));
        $totalGastos = MovimientoFinanciero::whereBetween('fecha', [$inicio, $fin])->sum('monto');

        return [
            Stat::make('💸 Total Prestado', '💲' . number_format($totalPrestado, 0, ',', '.')),
            Stat::make('💰 Total Cobrado', '💲' . number_format($totalCobrado, 0, ',', '.')),
            Stat::make('📈 Ganancia Estimada', '💲' . number_format($gananciaEstimada, 0, ',', '.')),
            Stat::make('💸 Total de Gastos', '💲' . number_format($totalGastos, 0, ',', '.')),
        ];
    }
}
