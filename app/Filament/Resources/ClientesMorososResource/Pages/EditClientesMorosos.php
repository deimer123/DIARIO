<?php

namespace App\Filament\Resources\ClientesMorososResource\Pages;

use App\Filament\Resources\ClientesMorososResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientesMorosos extends EditRecord
{
    protected static string $resource = ClientesMorososResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
