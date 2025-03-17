<?php

namespace App\Filament\Resources\CobrosDelDiaResource\Pages;

use App\Filament\Resources\CobrosDelDiaResource;
use Filament\Resources\Pages\ListRecords;

class ListCobrosDelDia extends ListRecords
{
    protected static string $resource = CobrosDelDiaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

