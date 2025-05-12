<?php

namespace App\Filament\Resources\PagosDelDiaResource\Pages;

use App\Filament\Resources\PagosDelDiaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPagosDelDia extends EditRecord
{
    protected static string $resource = PagosDelDiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
