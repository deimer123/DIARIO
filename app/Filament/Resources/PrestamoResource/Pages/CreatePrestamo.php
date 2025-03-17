<?php

namespace App\Filament\Resources\PrestamoResource\Pages;

use App\Filament\Resources\PrestamoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\Actions\ButtonAction;

class CreatePrestamo extends CreateRecord
{
    protected static string $resource = PrestamoResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }


    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(), // 🔹 Mantiene solo el botón "Crear"
            $this->getCancelFormAction(), // 🔹 Mantiene el botón "Cancelar"
        ];
    }

    protected function afterCreate(): void
{
    // Accede al registro creado
    $prestamo = $this->record;

    // Ejemplo: generar plan de pagos
    $plan = PrestamoResource::generarPlanDePago(
        $prestamo->fecha_inicio_pago,
        intval($prestamo->cuotas), // ✅ Convertir a número entero
        $prestamo->tipo_pago,
        $prestamo->cobrar_domingo ?? 'no' // 📌 Si es null, asignar 'no'
    );
    

    // Guardar el plan de pagos en la base de datos
    foreach ($plan as $pago) {
        $prestamo->planPagos()->create($pago);
    }
}
    
}


