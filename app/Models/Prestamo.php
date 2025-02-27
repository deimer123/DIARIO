<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'monto', 
        'cuotas',
        'cuota_diaria',
        'saldo_restante',
        'saldo_pagado',
        'fecha_prestamo',
        'fecha_inicio_pago',
        'estado',
        'tipo_pago',
    ];

    // Agregar total_cuotas en $appends
    protected $appends = ['cuotas_pagadas', 'total_cuotas'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    } 

    public function planPagos()
    {
        return $this->hasMany(PlanPago::class, 'prestamo_id'); 
    }

    // Método para contar las cuotas pagadas
    public function getCuotasPagadasAttribute()
    {
        return $this->planPagos()->where('estado', 'Pagado')->count();
    }

    // Método para contar el total de cuotas
    public function getTotalCuotasAttribute()
    {
        return $this->planPagos()->count();
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }



    protected static function boot()
    {
        parent::boot();

        // 📌 Cada vez que se crea un préstamo, se actualiza la base financiera
        static::created(function ($prestamo) {
            \Log::info("Nuevo préstamo registrado: $prestamo->monto");

            $interes = 1.2; // 🔹 20% de interés
            $montoConInteres = round($prestamo->monto * $interes, 2); // ✅ Redondeamos para evitar errores de precisión

            BaseFinanciera::actualizarBase($prestamo->monto, 'préstamo', $montoConInteres);
        });
    }
}
