<?php

namespace App\Filament\Resources\PagoResource\Pages;

use App\Filament\Resources\PagoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePago extends CreateRecord
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


    protected static string $resource = PagoResource::class;
}
