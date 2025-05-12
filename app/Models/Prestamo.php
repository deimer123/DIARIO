<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'user_id',
        'monto',
        'interes',
        'cuotas',
        'cuota_diaria',
        'saldo_restante',
        'saldo_pagado',
        'fecha_prestamo',
        'fecha_inicio_pago',
        'estado',
        'tipo_pago',
        'cobrar_domingo',
    ];

    protected $appends = ['cuotas_pagadas', 'total_cuotas'];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function scopeSoloPendientes($query)
{
    return $query->where('estado', 'pendiente');
}


    public function planPagos()
    {
        return $this->hasMany(PlanPago::class, 'prestamo_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cobrador()
{
    return $this->belongsTo(User::class, 'user_id'); // Asegura que el campo sea 'user_id'
}


    // Atributos calculados
    public function getCuotasPagadasAttribute()
    {
        return $this->planPagos()->where('estado', 'Pagado')->count();
    }

    public function getTotalCuotasAttribute()
    {
        return $this->planPagos()->count();
    }

    // 📌 Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        // 📌 Al crear un préstamo
        static::creating(function ($prestamo) {
            if (empty($prestamo->user_id)) {
                $prestamo->user_id = auth()->id();
            }

            $interesDecimal = $prestamo->interes / 100;
            $prestamo->saldo_restante = round($prestamo->monto + ($prestamo->monto * $interesDecimal), 2);
        });

        // 📌 Después de crear un préstamo, actualizar la base financiera
        static::created(function ($prestamo) {
            Log::info("✅ Nuevo préstamo registrado con interés {$prestamo->interes}%: {$prestamo->monto}");
            BaseFinanciera::actualizarBase();
        });

        // 📌 Antes de eliminar un préstamo, verificar si tiene pagos
        static::deleting(function ($prestamo) {
            if ($prestamo->pagos()->exists()) {
                // Enviar alerta en la interfaz en lugar de un error de código
                session()->flash('error', '❌ No se puede eliminar este préstamo porque ya tiene pagos registrados.');
                return false; // Evita que se elimine
            }

            Log::info("🔴 Eliminando préstamo ID: {$prestamo->id}, actualizando Base Financiera...");
        });

        // 📌 Después de eliminar un préstamo, actualizar la base financiera
        static::deleted(function ($prestamo) {
            BaseFinanciera::actualizarBase();
            Log::info("✅ Préstamo eliminado y Base Financiera actualizada.");
        });
    }
}
