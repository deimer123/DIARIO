<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BaseFinanciera extends Model
{
    use HasFactory;

    protected $table = 'base_financieras';
    protected $fillable = [
        'monto_disponible',
        'total_prestado',
        'total_pendiente',
        'ganancia',
        'base_inicial',
        'total_gastos_salidas',
        'balance_ajustado'
    ];

    public static function calcularBalanceAjustado()
    {
        $base = self::obtenerBase();
        $gastosSalidas = MovimientoFinanciero::whereIn('tipo', ['gasto', 'salida'])->sum('monto');

        $nuevoBalance = ($base->monto_disponible + $base->total_pendiente) - $gastosSalidas;
        $base->balance_ajustado = $nuevoBalance;
        $base->total_gastos_salidas = $gastosSalidas;
        $base->save();

        return $nuevoBalance;
    }

    public static function obtenerBase()
    {
        return self::firstOrCreate([], [
            'base_inicial' => 0,
            'monto_disponible' => 0,
            'total_prestado' => 0,
            'total_pendiente' => 0,
            'total_gastos_salidas' => 0,
            'balance_ajustado' => 0,
            'ganancia' => 0,
        ]);
    }

    /**
     * 📌 Recalcula toda la Base Financiera en función de los préstamos existentes.
     */
    public static function actualizarBase($monto = null, $tipo = null)
    {
        $base = self::obtenerBase();

        Log::info("🔄 Recalculando Base Financiera...");

        // 🔹 Recalcular valores con todos los préstamos y pagos
        $pagosRecibidos = Pago::sum('monto');
        $totalPrestado = Prestamo::sum('monto');
        $totalPendiente = Prestamo::sum('saldo_restante');

        // 🔥 **Corrección de la Ganancia**
        $ganancia = Prestamo::sum('saldo_restante') + Prestamo::sum('saldo_pagado') - Prestamo::sum('monto');

        // 📌 **Corregir el `total_pendiente`**
        if ($tipo === 'pago' && $monto !== null) {
            $base->total_pendiente -= $monto;
        } else {
            $base->total_pendiente = $totalPendiente;
        }

        $base->monto_disponible = $base->base_inicial + $pagosRecibidos - $totalPrestado;
        $base->total_prestado = $totalPrestado;
        $base->ganancia = max(0, $ganancia); // Evita valores negativos

        // 📌 **Recalcular el balance ajustado**
        $base->balance_ajustado = ($base->monto_disponible + $base->total_pendiente) - $base->total_gastos_salidas;

        Log::info("✅ Base Financiera actualizada: Disponible: {$base->monto_disponible}, Total Prestado: {$base->total_prestado}, Total Pendiente: {$base->total_pendiente}, Ganancia: {$base->ganancia}");

        $base->save();
    }
}
