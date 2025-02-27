<?php

namespace App\Filament\Resources\HistorialPrestamosResource\Pages;

use App\Filament\Resources\HistorialPrestamosResource;
use Filament\Resources\Pages\ListRecords;

class ListHistorialPrestamos extends ListRecords
{
    protected static string $resource = HistorialPrestamosResource::class; // 📌 Asegurar la referencia correcta
}