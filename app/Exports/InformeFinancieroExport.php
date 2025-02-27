<?php

namespace App\Exports;

use App\Models\Prestamo;
use App\Models\MovimientoFinanciero;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class InformeFinancieroExport implements FromCollection, WithHeadings, WithEvents
{
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio instanceof Carbon ? $fechaInicio : Carbon::parse($fechaInicio);
        $this->fechaFin = $fechaFin instanceof Carbon ? $fechaFin : Carbon::parse($fechaFin);
    }

    public function collection()
    {
        // 📌 Obtener préstamos dentro del rango de fechas
        $prestamos = Prestamo::whereBetween('fecha_prestamo', [$this->fechaInicio, $this->fechaFin])
            ->with('cliente')
            ->get()
            ->map(function ($prestamo) {
                return [
                    'Cliente' => $prestamo->cliente->nombre ?? 'Sin Cliente',
                    'Valor Prestado' => (float) $prestamo->monto, 
                    'Total Pagado' => (float) $prestamo->saldo_pagado,
                    'Ganancia' => (float) ($prestamo->saldo_pagado - $prestamo->monto),
                    'Estado' => $prestamo->saldo_restante == 0 ? 'Pagado' : 'Pendiente',
                    'Falta por Cobrar' => (float) $prestamo->saldo_restante,
                ];
            });

        // 📌 Obtener los movimientos financieros dentro del rango de fechas
        $movimientos = MovimientoFinanciero::whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])
            ->get()
            ->map(function ($movimiento) {
                return [
                    'Tipo' => ucfirst($movimiento->tipo),
                    'Monto' => (float) $movimiento->monto,
                    'Motivo' => $movimiento->motivo ?? 'Sin Motivo',
                    'Fecha' => $movimiento->fecha 
                        ? Carbon::parse($movimiento->fecha)->format('d-m-Y') 
                        : 'Fecha No Disponible',
                ];
            });

        // 📌 Totales de préstamos y ganancias
        $totalPrestado = $prestamos->sum('Valor Prestado');
        $totalCobrado = $prestamos->sum('Total Pagado');
        $totalGanancia = $prestamos->sum('Ganancia');

        $totalEntradas = $movimientos->where('Tipo', 'Entrada')->sum('Monto');
        $totalSalidas = $movimientos->where('Tipo', 'Salida')->sum('Monto');
        $totalGastos = $movimientos->where('Tipo', 'Gasto')->sum('Monto');

        // 📌 Cálculo del balance final
        $balanceFinal = ($totalCobrado + $totalEntradas) - ($totalSalidas + $totalGastos);

        // 📌 Construcción de la estructura final para el Excel con separaciones
        return collect([
            ['INFORME FINANCIERO'],
            ['Fecha Inicio:', $this->fechaInicio->format('d-m-Y')],
            ['Fecha Fin:', $this->fechaFin->format('d-m-Y')],
            [],
            ['PRÉSTAMOS'],
            ['Cliente', 'Valor Prestado', 'Total Pagado', 'Ganancia', 'Estado', 'Falta por Cobrar'],
        ])
        ->merge($prestamos)
        ->merge([
            [],
            ['RESUMEN FINANCIERO'],
            ['Total Prestado', 'Total Cobrado', 'Ganancia Total'],
            [$totalPrestado, $totalCobrado, $totalGanancia],
            [],
            ['MOVIMIENTOS FINANCIEROS'],
            [],
            ['Tipo', 'Monto', 'Motivo', 'Fecha'],
        ])
        ->merge($movimientos)
        ->merge([
            [],
            ['TOTAL MOVIMIENTOS'],
            ['Total Entradas', 'Total Salidas', 'Total Gastos'],
            [$totalEntradas, $totalSalidas, $totalGastos],
            [],
            ['BALANCE FINAL'],
            ['Disponible después de Movimientos', $balanceFinal],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 📌 Ajustar el ancho de las columnas automáticamente
                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // 📌 Negrita para el título del informe
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->mergeCells('A1:F1'); // Unir celdas del título

                

                // 📌 Aplicar bordes a las tablas de datos
                $lastRow = $event->sheet->getHighestRow();
                $sheet->getStyle("A5:F$lastRow")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // 📌 Centrar los títulos
                $sheet->getStyle("A1:A$lastRow")->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
