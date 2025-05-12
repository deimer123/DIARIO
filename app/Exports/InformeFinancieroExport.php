<?php

namespace App\Exports;

use App\Models\Prestamo;
use App\Models\Pago;
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
        $this->fechaInicio = $fechaInicio instanceof Carbon ? $fechaInicio->startOfDay() : Carbon::parse($fechaInicio)->startOfDay();
        $this->fechaFin = $fechaFin instanceof Carbon ? $fechaFin->endOfDay() : Carbon::parse($fechaFin)->endOfDay();
    }

    public function collection()
    {
        $fechasDePago = Pago::whereBetween('fecha_pago', [$this->fechaInicio, $this->fechaFin])
            ->orderBy('fecha_pago')
            ->pluck('fecha_pago')
            ->unique()
            ->map(fn($fecha) => Carbon::parse($fecha)->format('d-m-Y'))
            ->values()
            ->toArray();

        $encabezadosPagos = array_merge(['Cliente'], $fechasDePago);

        $prestamosConPagos = Prestamo::with(['cliente', 'pagos' => function ($q) {
                $q->whereBetween('fecha_pago', [$this->fechaInicio, $this->fechaFin])
                  ->orderBy('fecha_pago');
            }])
            ->whereDate('fecha_prestamo', '<=', $this->fechaFin)
            ->whereHas('pagos', function ($q) {
                $q->whereBetween('fecha_pago', [$this->fechaInicio, $this->fechaFin]);
            })
            ->get();

        $dataPagos = $prestamosConPagos->map(function ($prestamo) use ($fechasDePago) {
            $fila = [$prestamo->cliente->nombre ?? 'Sin Nombre'];
            $pagosPorFecha = $prestamo->pagos->groupBy(fn($p) => Carbon::parse($p->fecha_pago)->format('d-m-Y'));
            foreach ($fechasDePago as $fecha) {
                $monto = optional($pagosPorFecha->get($fecha))->sum('monto') ?? '';
                $fila[] = $monto > 0 ? $monto : '';
            }
            return $fila;
        });

        $prestamosEnRango = Prestamo::with('cliente')
            ->whereBetween('fecha_prestamo', [$this->fechaInicio, $this->fechaFin])
            ->get();

        $dataPrestamos = $prestamosEnRango->map(function ($p) {
            return [
                $p->id,
                $p->cliente->nombre ?? 'Sin Nombre',
                Carbon::parse($p->fecha_prestamo)->format('d-m-Y'),
                $p->monto,
                $p->saldo_pagado,
                $p->saldo_restante,
                $p->interes,
                $p->monto * ($p->interes / 100),
                ucfirst($p->estado),
            ];
        });

        $totalPrestado = $prestamosEnRango->sum('monto');
        $totalCobrado = Pago::whereBetween('fecha_pago', [$this->fechaInicio, $this->fechaFin])->sum('monto');
        $gananciaTotal = $prestamosEnRango->sum(fn($p) => $p->monto * ($p->interes / 100));

        $gastos = MovimientoFinanciero::whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])->orderBy('fecha')->get();
        $totalGastos = $gastos->sum('monto');

        $gastosData = $gastos->map(function ($gasto) {
            return [
                Carbon::parse($gasto->fecha)->format('d-m-Y'),
                $gasto->motivo,
                $gasto->monto
            ];
        });

        return collect([
            ['REPORTE FINANCIERO'],
            [''],
            ['Fecha Inicio:', $this->fechaInicio->format('d-m-Y')],
            ['Fecha Fin:', $this->fechaFin->format('d-m-Y')],
            [''],
            ['📄 PRÉSTAMOS EN EL RANGO'],
            [''],
            ['ID', 'Cliente', 'Fecha del Préstamo', 'Monto', 'Saldo Pagado', 'Saldo por Pagar', 'Interés (%)', 'Ganancia', 'Estado'],
        ])
        ->merge($dataPrestamos)
        ->merge([
            [''],
            ['📌 PRÉSTAMOS CON PAGOS'],
            [''],
            $encabezadosPagos,
        ])
        ->merge($dataPagos)
        ->merge([
            [''],
            ['📉 GASTOS EN EL RANGO'],
            [''],
            ['Fecha', 'Motivo', 'Monto'],
        ])
        ->merge($gastosData)
        ->merge([
            [''],
            ['📊 RESUMEN FINANCIERO'],
            [''],
            ['Total Prestado en el rango:', $totalPrestado],
            ['Total Cobrado en el rango:', $totalCobrado],
            ['Total de gastos:', $totalGastos],
            ['Ganancia estimada:', $gananciaTotal],
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
            $lastColumn = $sheet->getHighestColumn();
            $lastRow = $sheet->getHighestRow();

            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastColumn);
            for ($i = 1; $i <= $highestColumnIndex; $i++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->mergeCells("A1:G1");

            // Colores únicos por tabla
            $colorPrestamos = 'D9EAD3';       // Verde claro
            $colorPagos = 'FCE5CD';           // Naranja claro
            $colorGastos = 'C9DAF8';          // Azul claro
            $colorResumen = 'F4CCCC';         // Rojo claro

            for ($fila = 1; $fila <= $lastRow; $fila++) {
                $valor = $sheet->getCell("A{$fila}")->getValue();

                if ($valor === '📄 PRÉSTAMOS EN EL RANGO') {
                    $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(13);
                    $headerRow = $fila + 2;
                    $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($colorPrestamos);
                    $sheet->getStyle("A{$headerRow}:I{$lastRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }

                if ($valor === '📌 PRÉSTAMOS CON PAGOS') {
                    $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(13);
                    $encabezadoRow = $fila + 2;
                    $sheet->getStyle("A{$encabezadoRow}:{$lastColumn}{$encabezadoRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$encabezadoRow}:{$lastColumn}{$encabezadoRow}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($colorPagos);
                    $sheet->getStyle("A{$encabezadoRow}:{$lastColumn}{$lastRow}")
                        ->getAlignment()->setWrapText(true);
                    $sheet->getStyle("A{$encabezadoRow}:{$lastColumn}{$lastRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }

                if ($valor === '📉 GASTOS EN EL RANGO') {
                    $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(13);
                    $headerRow = $fila + 2;
                    $sheet->getStyle("A{$headerRow}:C{$headerRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$headerRow}:C{$headerRow}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($colorGastos);
                    $sheet->getStyle("A{$headerRow}:C{$lastRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }

                if ($valor === '📊 RESUMEN FINANCIERO') {
                    $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(13);
                    for ($i = 1; $i <= 4; $i++) {
                        $resumenRow = $fila + $i + 1;
                        $sheet->getStyle("A{$resumenRow}:B{$resumenRow}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$resumenRow}:B{$resumenRow}")->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($colorResumen);
                    }
                }
            }
        },
    ];
}

}
