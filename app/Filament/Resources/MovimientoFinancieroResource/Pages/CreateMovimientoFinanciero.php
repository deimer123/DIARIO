<?php

namespace App\Filament\Resources\MovimientoFinancieroResource\Pages;

use App\Filament\Resources\MovimientoFinancieroResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMovimientoFinanciero extends CreateRecord
{

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


    protected static string $resource = MovimientoFinancieroResource::class;
}
