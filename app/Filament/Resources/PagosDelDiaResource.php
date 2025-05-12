<?php

namespace App\Filament\Resources;

use App\Models\Pago;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Filament\Resources\PagosDelDiaResource\Pages;

class PagosDelDiaResource extends Resource
{
    protected static ?string $model = Pago::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Pagos del Día';
    protected static ?string $pluralLabel = 'Pagos del Día';
    protected static ?string $slug = 'pagos-del-dia';

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => Pago::with('prestamo.cliente')->whereDate('fecha_pago', today()))
            ->columns([
                Tables\Columns\TextColumn::make('prestamo.cliente.nombre')
                    ->label('😎 Cliente')
                   // ->icon('heroicon-o-user')
                    ->sortable()
                    ->searchable()
                    //->color('primary')
                    ->weight('bold')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('monto')
                    ->label('💰 Valor de la Cuota')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                    ->prefix('💲')
                    ->grow(false)
                    ->alignCenter()
                    ->color('success')
                    ->weight('bold'),

                

                Tables\Columns\TextColumn::make('prestamo_id')
                    ->label('📑 ID Préstamo')
                    ->prefix('#️⃣ ')
                    ->alignCenter()
                    ->color('gray')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('fecha_pago')
                    ->label('📅 Fecha')
                    ->date('d-m-Y')
                    ->alignCenter()
                    ->color('gray'),
            ])
            ->actions([
              //  Tables\Actions\Action::make('Registrar Pago')
                  //  ->label('Registrar Pago')
                 //   ->icon('heroicon-o-banknotes')
                  //  ->color('success')
                  //  ->url(fn ($record) => \App\Filament\Resources\PagoResource::getUrl('create', ['prestamo_id' => $record->prestamo_id]))
                //    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('🫤 Sin Pagos Hoy')
            ->emptyStateDescription('Aún no se han realizado pagos en esta fecha.')
            ->striped()
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagosDelDia::route('/'),
        ];
    }
}
