<?php

namespace App\Filament\Resources\PrestamoResource\Pages;

use App\Filament\Resources\PrestamoResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions;

class ViewPrestamo extends ViewRecord
{
    protected static string $resource = PrestamoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
              ];
    }
}
