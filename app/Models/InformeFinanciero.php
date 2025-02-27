<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformeFinanciero extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'monto_prestado', 'monto_pagado', 'ganancia', 'estado'];

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Método para actualizar el informe financiero después de un pago
    public static function actualizarInforme($clienteId, $montoPago)
    {
        $informe = self::where('cliente_id', $clienteId)->first();

        if ($informe) {
            $informe->monto_pagado += $montoPago;
            $informe->ganancia += $montoPago * 0.2; // ✅ Ejemplo: 10% de ganancia
            if ($informe->monto_pagado >= $informe->monto_prestado) {
                $informe->estado = 'Pagado';
            }
            $informe->save();
        }
    }
}
