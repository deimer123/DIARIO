<?php

namespace App\Filament\Resources\BaseFinancieraResource\Pages;

use App\Filament\Resources\BaseFinancieraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBaseFinancieras extends ListRecords
{
    protected static string $resource = BaseFinancieraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
