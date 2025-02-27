<?php

namespace App\Filament\Resources\MovimientoFinancieroResource\Pages;

use App\Filament\Resources\MovimientoFinancieroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovimientoFinancieros extends ListRecords
{
    protected static string $resource = MovimientoFinancieroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
