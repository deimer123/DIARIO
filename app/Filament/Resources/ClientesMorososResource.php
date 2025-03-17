<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesMorososResource\Pages;
use App\Models\Prestamo;
use App\Models\PlanPago;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ClientesMorososResource extends Resource
{
    protected static ?string $model = Prestamo::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $navigationLabel = '🚨 Clientes Morosos';
    protected static ?int $navigationSort = 2; // Posición en el menú
    protected static ?string $pluralLabel = 'Clientes Morosos';

    public static function getEloquentQuery(): Builder
{ 


    return Prestamo::whereHas('planPagos', function ($query) {
            $query->where('estado', 'Pendiente')
                  ->where('fecha', '<', now()); // Solo cuotas vencidas
        })

        
        ->withCount([
            'planPagos as cuotas_vencidas' => function ($query) {
                $query->where('estado', 'Pendiente')
                      ->where('fecha', '<', now());
            }
        ])
        ->when(!auth()->user()->hasRole('Administrador'), function ($query) {
            $query->where('user_id', auth()->id()); // ✅ Solo préstamos del cobrador autenticado
        })
        ->with(['cobrador']); // ✅ Cargar cobrador correctamente

       

        
}


    public static function table(Tables\Table $table): Tables\Table
{
    return $table
    ->striped() // Alterna colores en las filas
        ->paginated(false) // ✅ Desactiva la paginación
        ->columns([
            Tables\Columns\TextColumn::make('cliente.nombre')
                ->label('😎 Cliente')
                ->sortable()
                ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                ->label('🧑‍💼 Cobrador')
                ->prefix('👺 ')
                ->grow(false)
                ->alignCenter()
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->hidden(fn () => !auth()->user()->hasRole('Administrador')),

                Tables\Columns\TextColumn::make('id') // ✅ Esto mostrará el ID del préstamo correctamente
    ->label('📜 ID Préstamo')
    ->toggleable()
    ->toggledHiddenByDefault(true)
    ->prefix('#️⃣ '),



                

            Tables\Columns\TextColumn::make('cuotas_vencidas')
                ->label('📅 Cuotas Vencidas')
                ->sortable()
                ->color('danger')
                ->formatStateUsing(fn ($state) => "⚠️ {$state} cuotas"),

            Tables\Columns\TextColumn::make('monto_mora')
                ->label('💰 Deuda Total')
                ->getStateUsing(fn (Prestamo $record) => 
                    '💲 ' . number_format($record->cuotas_vencidas * $record->cuota_diaria, 2)
                )
                ->sortable(),

            Tables\Columns\TextColumn::make('cuota_diaria')
                ->label('💵 Cuota Diaria')
                ->sortable()
                ->toggleable()
    ->toggledHiddenByDefault(true)
                ->formatStateUsing(fn ($state) => "💲 " . number_format($state, 2)),

            Tables\Columns\BadgeColumn::make('estado')
                ->label('⏳ Estado')
                ->colors([
                    'danger' => 'Mora',
                    'warning' => 'Pendiente',
                    'success' => 'Pagado',
                ]),
        ])
        ->actions([
            Tables\Actions\Action::make('Realizar Pago')
                ->label('💰 Pagar')
                ->url(fn (Prestamo $record) => PagoResource::getUrl('create', ['prestamo_id' => $record->id])),
        ])
        ->filters([
            //
        ])
        ->striped();
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientesMorosos::route('/'),
        ];
    }
}
