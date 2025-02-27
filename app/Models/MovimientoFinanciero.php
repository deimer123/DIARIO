<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoFinanciero extends Model
{
    use HasFactory;

    protected $fillable = ['tipo', 'monto', 'motivo', 'fecha'];

    protected static function boot()
    {
        parent::boot();

        // 📌 Cada vez que se crea un movimiento financiero, se actualiza la Base Financiera
        static::created(function ($movimiento) {
            $base = BaseFinanciera::firstOrCreate([], [
                'monto_disponible' => 0,
                'total_gastos_salidas' => 0,
                'balance_ajustado' => 0,
            ]);

            // Actualizar monto disponible según el tipo de movimiento
            if ($movimiento->tipo === 'entrada') {
                $base->monto_disponible += $movimiento->monto; // 🟢 Aumenta
            } elseif ($movimiento->tipo === 'salida' || $movimiento->tipo === 'gasto') {
                $base->monto_disponible -= $movimiento->monto; // 🔴 Disminuye
            }

            // 🔹 Calcular y actualizar el total de gastos/salidas
            $base->total_gastos_salidas = MovimientoFinanciero::whereIn('tipo', ['gasto', 'salida'])->sum('monto');

            // 🔹 Calcular el nuevo balance ajustado
            $base->balance_ajustado = BaseFinanciera::calcularBalanceAjustado();

            // 🔹 Guardar los cambios en la base financiera
            $base->save();
        });
    }
}
