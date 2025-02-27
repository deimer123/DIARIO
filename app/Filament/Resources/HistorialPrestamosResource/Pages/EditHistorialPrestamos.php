<?php

namespace App\Filament\Resources\HistorialPrestamosResource\Pages;

use App\Filament\Resources\HistorialPrestamosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHistorialPrestamos extends EditRecord
{
    protected static string $resource = HistorialPrestamosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
