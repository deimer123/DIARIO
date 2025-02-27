<?php

namespace App\Filament\Resources\MovimientoFinancieroResource\Pages;

use App\Filament\Resources\MovimientoFinancieroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovimientoFinanciero extends EditRecord
{
    protected static string $resource = MovimientoFinancieroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
