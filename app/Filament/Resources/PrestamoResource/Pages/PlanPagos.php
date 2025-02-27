<?php

namespace App\Filament\Resources\PrestamoResource\Pages;

use App\Filament\Resources\PrestamoResource;
use Filament\Resources\Pages\Page;
use App\Models\Prestamo;

class PlanPagos extends Page
{
    protected static string $resource = PrestamoResource::class;

    protected static string $view = 'filament.resources.prestamo-resource.pages.plan-pagos';

    public Prestamo $prestamo;

    public function mount($record): void
    {
        $this->prestamo = Prestamo::findOrFail($record);
    }
}