<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;




class CreateCliente extends CreateRecord
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






    protected static string $resource = ClienteResource::class;
}
