<?php

namespace App\Filament\Resources\InformeFinancieroResource\Pages;

use App\Filament\Resources\InformeFinancieroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInformeFinancieros extends ListRecords
{
    protected static string $resource = InformeFinancieroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
