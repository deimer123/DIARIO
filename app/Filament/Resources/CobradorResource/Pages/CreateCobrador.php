<?php

namespace App\Filament\Resources\CobradorResource\Pages;

use App\Filament\Resources\CobradorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCobrador extends CreateRecord
{
    protected static string $resource = CobradorResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    public function getTitle(): string
    {
        return 'Crear Cobrador'; // 🔹 Cambia el título
    }


    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(), // 🔹 Mantiene solo el botón "Crear"
            $this->getCancelFormAction(), // 🔹 Mantiene el botón "Cancelar"
        ];
    }
}
