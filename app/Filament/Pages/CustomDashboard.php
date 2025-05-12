<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class CustomDashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí puedes agregar widgets tipo resumen o gráficas si los tienes
            // \App\Filament\Widgets\ResumenDiarioWidget::class,
        ];
    }
}
