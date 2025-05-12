<?php

namespace App\Filament\Resources\PagosDelDiaResource\Pages;

use App\Filament\Resources\PagosDelDiaResource;
use Filament\Resources\Pages\ListRecords;

class ListPagosDelDia extends ListRecords
{
    protected static string $resource = PagosDelDiaResource::class;

    // ✅ Este método debe ser público
    public function getTitle(): string
    {
        return 'Pagos del Día';
    }
}
