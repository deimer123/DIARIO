<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestamo_id',
        'monto',
        'fecha_pago',
        'user_id',
    ];

    protected $with = ['prestamo.cliente']; // Esto carga siempre la relación


    public function prestamo()
{
    return $this->belongsTo(Prestamo::class);
}

public function cliente()
{
    return $this->hasOneThrough(Cliente::class, Prestamo::class, 'id', 'id', 'prestamo_id', 'cliente_id');
}

    protected static function booted()
{
    static::created(function ($pago) {
        $prestamo = $pago->prestamo;
        $montoPendiente = $pago->monto;

        // 1️⃣ Obtener el total pagado del préstamo
$totalPagado = $prestamo->pagos()->sum('monto'); 

// 2️⃣ Obtener todas las cuotas ordenadas por fecha
$cuotas = $prestamo->planPagos()->orderBy('fecha')->get();

// 3️⃣ Calcular cuántas cuotas se pueden cubrir con el total pagado
$cuotasCubiertas = intval($totalPagado / $prestamo->cuota_diaria);

// 4️⃣ Actualizar el estado de las cuotas según el total pagado
foreach ($cuotas as $index => $cuota) {
    if ($index < $cuotasCubiertas) {
        $cuota->estado = 'Pagado';
    } else {
        $cuota->estado = 'Pendiente';
    }
    $cuota->save();
}

// 5️⃣ Verificar si todas las cuotas han sido pagadas
$totalCuotas = $cuotas->count();
if ($cuotasCubiertas >= $totalCuotas) {
    // Si se han cubierto todas las cuotas, marcar el préstamo como pagado
    $prestamo->update(['estado' => 'Pagado']);
}


       


        // Descontar el monto del saldo restante en el préstamo
        $prestamo->saldo_restante -= $pago->monto;
        $prestamo->saldo_pagado += $pago->monto;

        if ($prestamo->saldo_restante <= 0) {
            $prestamo->saldo_restante = 0;
            $prestamo->estado = 'Pagado';
        }

        $prestamo->save();


        Log::info("✅ Nuevo saldo después del pago: {$prestamo->saldo_restante}");

            // 🔥 **Actualizar la Base Financiera con el tipo 'pago'**
            BaseFinanciera::actualizarBase($pago->monto, 'pago');
    });

    static::deleted(function ($pago) {
    $prestamo = $pago->prestamo;

    // Revertir el monto
    $prestamo->saldo_restante += $pago->monto;
    $prestamo->saldo_pagado -= $pago->monto;

    // Asegurarse de que no quede en negativo
    if ($prestamo->saldo_pagado < 0) {
        $prestamo->saldo_pagado = 0;
    }

    // Recalcular el estado del préstamo
    $prestamo->estado = 'Pendiente';

    // Recalcular el estado de las cuotas
    $totalPagado = $prestamo->pagos()->sum('monto') - $pago->monto; // Restamos el que se borró
    $cuotas = $prestamo->planPagos()->orderBy('fecha')->get();
    $cuotasCubiertas = intval($totalPagado / $prestamo->cuota_diaria);

    foreach ($cuotas as $index => $cuota) {
        $cuota->estado = ($index < $cuotasCubiertas) ? 'Pagado' : 'Pendiente';
        $cuota->save();
    }

    $prestamo->save();

    Log::info("⛔️ Pago eliminado. Nuevo saldo restante: {$prestamo->saldo_restante}");
});
static::updated(function ($pago) {
    $prestamo = $pago->prestamo;

    // 🧠 Recuperamos el monto original antes de la edición
    $originalMonto = $pago->getOriginal('monto');

    // 🧮 Ajustar el saldo del préstamo
    $prestamo->saldo_restante += $originalMonto; // Revertir el anterior
    $prestamo->saldo_pagado -= $originalMonto;

    $prestamo->saldo_restante -= $pago->monto; // Aplicar el nuevo
    $prestamo->saldo_pagado += $pago->monto;

    // Limitar por seguridad
    if ($prestamo->saldo_restante < 0) {
        $prestamo->saldo_restante = 0;
    }
    if ($prestamo->saldo_pagado < 0) {
        $prestamo->saldo_pagado = 0;
    }

    // Recalcular cuotas
    $totalPagado = $prestamo->pagos()->sum('monto'); // Ya incluye el monto actualizado
    $cuotas = $prestamo->planPagos()->orderBy('fecha')->get();
    $cuotasCubiertas = intval($totalPagado / $prestamo->cuota_diaria);

    foreach ($cuotas as $index => $cuota) {
        $cuota->estado = ($index < $cuotasCubiertas) ? 'Pagado' : 'Pendiente';
        $cuota->save();
    }

    // Estado del préstamo
    $totalCuotas = $cuotas->count();
    if ($cuotasCubiertas >= $totalCuotas || $prestamo->saldo_restante <= 0) {
        $prestamo->estado = 'Pagado';
        $prestamo->saldo_restante = 0;
    } else {
        $prestamo->estado = 'Pendiente';
    }

    $prestamo->save();

    Log::info("✏️ Pago editado. Nuevo saldo: {$prestamo->saldo_restante}");
});


}





    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

}