<?php

namespace App\Filament\Resources\CobrosDelDiaResource\Pages;

use App\Filament\Resources\CobrosDelDiaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCobrosDelDia extends EditRecord
{
    protected static string $resource = CobrosDelDiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
