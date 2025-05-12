<?php

namespace App\Filament\Resources\ClientesConCreditoVencidoResource\Pages;

use App\Filament\Resources\ClientesConCreditoVencidoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientesConCreditoVencido extends EditRecord
{
    protected static string $resource = ClientesConCreditoVencidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
