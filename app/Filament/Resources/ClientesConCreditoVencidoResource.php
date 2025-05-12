<?php

namespace App\Filament\Resources;

use App\Models\Prestamo;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ClientesConCreditoVencidoResource\Pages;

class ClientesConCreditoVencidoResource extends Resource
{
    protected static ?string $model = Prestamo::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';
    protected static ?string $navigationLabel = 'Créditos Vencidos';
    protected static ?int $navigationSort = 3;
    protected static ?string $pluralLabel = 'Clientes con Crédito Vencido';

    public static function getEloquentQuery(): Builder
    {
        return Prestamo::whereHas('planPagos', function ($query) {
                $query->selectRaw('MAX(fecha) as max_fecha, prestamo_id')
                      ->groupBy('prestamo_id');
            })
            ->whereHas('planPagos', function ($query) {
                $query->where('estado', 'Pendiente')
                      ->whereIn('fecha', function ($subQuery) {
                          $subQuery->selectRaw('MAX(fecha)')
                                   ->from('plan_pagos')
                                   ->whereColumn('prestamo_id', 'prestamos.id')
                                   ->groupBy('prestamo_id');
                      })
                      ->where('fecha', '<', now());
            })
            ->when(!auth()->user()->hasRole('Administrador'), function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['cliente', 'user']);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->striped()
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('👤 Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('🧑‍💼 Cobrador')
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->hidden(fn () => !auth()->user()->hasRole('Administrador')),

                Tables\Columns\TextColumn::make('id')
                    ->label('📄 ID Préstamo')
                    ->prefix('#️⃣ '),

                Tables\Columns\TextColumn::make('planPagos.max_fecha')
                    ->label('📆 Última Cuota')
                    ->getStateUsing(fn ($record) => optional($record->planPagos->sortByDesc('fecha')->first())->fecha 
    ? \Carbon\Carbon::parse($record->planPagos->sortByDesc('fecha')->first()->fecha)->format('d/m/Y')
    : '—'
)

                    ->sortable(),

                Tables\Columns\TextColumn::make('cuota_diaria')
                    ->label('💵 Cuota Diaria')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                    ->prefix('💲')
                    ->color('success'),

            ])
            ->actions([
                Tables\Actions\Action::make('Ir a Pagos')
                ->label('💰 Pagar')
                ->url(fn () => PagoResource::getUrl('index')),
                //->openUrlInNewTab(), // opcional
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientesConCreditoVencido::route('/'),
        ];
    }
}
