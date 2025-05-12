<?php

namespace App\Filament\Resources\PlanPagoResource\Pages;

use App\Filament\Resources\PlanPagoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanPagos extends ListRecords
{
    protected static string $resource = PlanPagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // Actions\CreateAction::make(),
        ];
    }
}
