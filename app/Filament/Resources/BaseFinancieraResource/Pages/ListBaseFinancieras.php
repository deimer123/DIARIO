<?php

namespace App\Filament\Resources\BaseFinancieraResource\Pages;

use App\Filament\Resources\BaseFinancieraResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Livewire\Livewire;

class ListBaseFinancieras extends ListRecords
{
    protected static string $resource = BaseFinancieraResource::class;

    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filtrar')
                ->label('📅 Filtrar por fechas')
                ->form([
                    DatePicker::make('fecha_inicio')->label('Desde')->required(),
                    DatePicker::make('fecha_fin')->label('Hasta')->required(),
                ])
                ->action(function (array $data): void {
                    $this->fechaInicio = $data['fecha_inicio'];
                    $this->fechaFin = $data['fecha_fin'];

                    // Emitimos evento para actualizar el widget
                    $this->dispatch('actualizarEstadisticas', $this->fechaInicio, $this->fechaFin);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\BaseFinancieraResource\Widgets\BaseFinancieraOverview::class,
        ];
    }
}
