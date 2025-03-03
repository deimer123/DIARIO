<?php

namespace App\Filament\Resources;

use App\Models\InformeFinanciero;
use App\Filament\Resources\InformeFinancieroResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InformeFinancieroExport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
 

class InformeFinancieroResource extends Resource
{
    protected static ?string $model = InformeFinanciero::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cliente_id')->label('Cliente'),
                TextColumn::make('monto_prestado')->label('Monto Prestado')->sortable(),
                TextColumn::make('monto_pagado')->label('Monto Pagado')->sortable(),
                TextColumn::make('ganancia')->label('Ganancia')->sortable(),
                TextColumn::make('estado')->label('Estado'),
            ])
            ->headerActions([
                Action::make('exportar')
                    ->label('📤 Exportar a Excel')
                    ->form([
                        DatePicker::make('fecha_inicio')
                            ->label('📅 Fecha de Inicio')
                            ->required(),
                        DatePicker::make('fecha_fin')
                            ->label('📅 Fecha de Fin')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Asegurar que las fechas sean instancias de Carbon antes de usarlas
                        $fechaInicio = $data['fecha_inicio'] instanceof Carbon ? $data['fecha_inicio'] : Carbon::parse($data['fecha_inicio']);
                        $fechaFin = $data['fecha_fin'] instanceof Carbon ? $data['fecha_fin'] : Carbon::parse($data['fecha_fin']);

                        return Excel::download(
                            new InformeFinancieroExport($fechaInicio, $fechaFin),
                            'informe_financiero.xlsx'
                        );
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInformeFinancieros::route('/'),
            'create' => Pages\CreateInformeFinanciero::route('/create'),
            'edit' => Pages\EditInformeFinanciero::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
{
    return auth()->user()->hasRole('Administrador'); // Solo admin puede ver
}
public static function canEdit(Model $record): bool
{
    return auth()->user()->hasRole('Administrador'); // Solo admin puede editar
}
public static function canDelete(Model $record): bool
{
    return auth()->user()->hasRole('Administrador'); // Solo admin puede eliminar
}

}
