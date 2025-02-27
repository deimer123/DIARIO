<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseFinanciera extends Model
{
    use HasFactory;

    protected $table = 'base_financieras';
    protected $fillable = ['monto_disponible', 'total_prestado', 'total_pendiente', 'ganancia','base_inicial','total_gastos_salidas','balance_ajustado'];


    public static function calcularBalanceAjustado()
    {
        $base = self::obtenerBase();
    $gastosSalidas = MovimientoFinanciero::whereIn('tipo', ['gasto', 'salida'])->sum('monto');

    // 🔹 Calculamos el nuevo balance ajustado
    $nuevoBalance = ($base->monto_disponible + $base->total_pendiente) - $gastosSalidas;

    // 🔹 Guardamos el nuevo balance en la base de datos
    $base->balance_ajustado = $nuevoBalance;
    $base->total_gastos_salidas = $gastosSalidas;
    $base->save();

    return $nuevoBalance;

    }

    public static function obtenerBase()
    {
        return self::firstOrCreate([], [
            'base_inicial' => 0,  // 🔹 Valor inicial de referencia
            'monto_disponible' => 0,
            'total_prestado' => 0,
            'total_pendiente' => 0,
            'total_gastos_salidas' => 0,
            'balance_ajustado' => 0,
            'ganancia' => 0,
        ]);
    }



    /**
     * 📌 Actualiza la base financiera según los movimientos realizados.
     */
    public static function actualizarBase($monto, $tipo)
    {
        $base = self::firstOrCreate([], [
            'base_inicial' => 0, 
            'monto_disponible' => 0,
            'total_prestado' => 0,
            'total_pendiente' => 0,
            'total_gastos_salidas' => 0,
            'balance_ajustado' => 0,
            'ganancia' => 0,
        ]);

        \Log::info("Actualizando Base Financiera - Tipo: $tipo - Monto: $monto");

        switch ($tipo) {
            case 'préstamo':
                $montoConInteres = $montoConInteres ?? ($monto * 1.2); // 🔹 Si no se pasa, calcularlo aquí
                $base->monto_disponible -= $monto;
                $base->total_prestado += $monto;
                $base->total_pendiente += $montoConInteres; // 🔹 Usa el monto con interés si está disponible
                $base->ganancia += ($montoConInteres - $monto);
                break;

            case 'pago':
                $base->monto_disponible += $monto;
                $base->total_pendiente -= $monto;
                
                break;

            case 'entrada':
                $base->monto_disponible += $monto;
                break;

            case 'salida':
                case 'gasto':
                    $base->monto_disponible -= $monto;
                    $base->total_gastos_salidas = MovimientoFinanciero::whereIn('tipo', ['gasto', 'salida'])->sum('monto'); 
                    break;
        
        }

        $base->balance_ajustado = ($base->monto_disponible + $base->total_pendiente) - $base->total_gastos_salidas;

        \Log::info("Nuevo Balance Ajustado: " . $base->balance_ajustado);
        \Log::info("Nuevo Monto Disponible: " . $base->monto_disponible);
        \Log::info("Nuevo Total Prestado: " . $base->total_prestado);
        \Log::info("Nueva Ganancia: " . $base->ganancia);

        $base->save();
    }
}
