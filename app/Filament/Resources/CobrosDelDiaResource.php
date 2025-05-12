<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CobrosDelDiaResource\Pages;
use App\Models\Prestamo;
use App\Models\PlanPago;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CobrosDelDiaResource extends Resource
{
    protected static ?string $model = PlanPago::class; // Se basa en el Plan de Pagos

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Cobros del Día';
    protected static ?int $navigationSort = 1; // Posición en el menú
    protected static ?string $pluralLabel = 'Cobros Del Día';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereDate('fecha', Carbon::today()) // Filtra las cuotas con fecha de hoy
            ->where('estado', 'Pendiente') // Solo muestra las pendientes
            ->with('prestamo.cliente') // Relaciona el préstamo y cliente
            ->whereHas('prestamo', function ($query) {
                $query->where('user_id', auth()->id()); // ✅ Filtrar por el creador del préstamo
            });
            
           // ->with(['cobrador']);


           
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
        ->striped() // Alterna colores en las filas
        ->paginated(false) // ✅ Desactiva la paginación
            ->columns([
                Tables\Columns\TextColumn::make('prestamo.cliente.nombre')
                    ->label('😎 Cliente')
                    ->sortable()
                    ->searchable()
                    ->prefix('👤 '),

                    Tables\Columns\TextColumn::make('prestamo.user.name') // 🔹 Aquí mostramos el cobrador
                    ->label('🧑‍💼 Cobrador')
                ->prefix('👺 ')
                ->grow(false)
                ->alignCenter()
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->hidden(fn () => !auth()->user()->hasRole('Administrador')),

                Tables\Columns\TextColumn::make('prestamo.id')
                    ->label('📜 ID Préstamo')
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->prefix('#️⃣ '),

                
                Tables\Columns\TextColumn::make('prestamo.cuota_diaria')
                    ->label('💰 Valor de la Cuota')
                    
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->alignCenter(), // Alinea el texto a la derecha

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('🔍 Estado')
                    ->colors([
                        'danger' => 'Pendiente',
                        'success' => 'Pagado',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'Pendiente' ? '⏳ Pendiente' : '✅ Pagado'),
            ])
            ->actions([
                Action::make('Registrar Pago')
                    ->label('💸 Registrar Pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->url(fn () => PagoResource::getUrl('index')),
            ])
            ->striped(); // 🔹 Agregar filas con estilo alterno
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCobrosDelDia::route('/'),
        ];
    }
}
