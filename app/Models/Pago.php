<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

        // Obtener cuotas pendientes ordenadas por fecha más próxima
        $cuotasPendientes = $prestamo->planPagos()->where('estado', 'Pendiente')->orderBy('fecha')->get();

        foreach ($cuotasPendientes as $cuota) {
            if ($montoPendiente <= 0) {
                break;
            }

            // Si el pago cubre toda la cuota
            if ($montoPendiente >= $prestamo->cuota_diaria) {
                $montoPendiente -= $prestamo->cuota_diaria;
                $cuota->estado = 'Pagado';
            } else {
                // Si el pago solo cubre una parte de la cuota
                $cuota->estado = 'Pendiente'; // No marcarla como pagada completamente
            }

            $cuota->save();
        }

        // Descontar el monto del saldo restante en el préstamo
        $prestamo->saldo_restante -= $pago->monto;
        $prestamo->saldo_pagado += $pago->monto;

        if ($prestamo->saldo_restante <= 0) {
            $prestamo->saldo_restante = 0;
            $prestamo->estado = 'Pagado';
        }

        $prestamo->save();
    });
}



protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $model->user_id = auth()->id(); // Asigna el usuario autenticado
            }
        });

        // 📌 Cada vez que se registra un pago, se actualiza la base financiera
        static::created(function ($pago) {
            \Log::info("Pago recibido: $pago->monto");

            BaseFinanciera::actualizarBase($pago->monto, 'pago');
        });
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

}
