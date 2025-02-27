<?php

namespace App\Filament\Resources\PlanPagoResource\Pages;

use App\Filament\Resources\PlanPagoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanPago extends EditRecord
{
    protected static string $resource = PlanPagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
