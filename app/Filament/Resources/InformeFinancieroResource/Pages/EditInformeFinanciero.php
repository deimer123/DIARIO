<?php

namespace App\Filament\Resources\InformeFinancieroResource\Pages;

use App\Filament\Resources\InformeFinancieroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInformeFinanciero extends EditRecord
{
    protected static string $resource = InformeFinancieroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
