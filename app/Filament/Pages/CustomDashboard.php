<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class CustomDashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return []; // Esto elimina todas las tarjetas del Dashboard
    }
}
