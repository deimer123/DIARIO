<?php

namespace App\Filament\Resources\CobradorResource\Pages;

use App\Filament\Resources\CobradorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCobradors extends ListRecords
{
    protected static string $resource = CobradorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
