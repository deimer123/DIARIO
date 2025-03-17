<?php

namespace App\Filament\Resources;

use App\Models\Prestamo;  // Asegurar que el modelo está importado
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\HistorialPrestamosResource\Pages\ListHistorialPrestamos;
use Carbon\Carbon;






class HistorialPrestamosResource extends Resource
{
    protected static ?string $model = Prestamo::class;  // 📌 Definir el modelo correctamente
    protected static ?string $navigationIcon = 'heroicon-o-collection';

    // 🔴 Deshabilita la opción de crear nuevos registros
    public static function canCreate(): bool
    {
        return false;
    }

    // 🔴 Deshabilita la opción de editar registros
    public static function canEdit($record): bool
    {
        return false;
    }

    // 🔴 Deshabilita la opción de eliminar registros
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
        ->query(fn () => Prestamo::where('cliente_id', request()->query('cliente_id'))) // 🔥 FILTRA POR CLIENTE SELECCIONADO
        ->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('📌 ID Préstamo')
                ->prefix('💳 ')
                ->grow(false)
                ->alignCenter(),
               

                

            Tables\Columns\TextColumn::make('cliente.nombre')
                ->label('🧑‍💼 Cliente')
                ->prefix('🗣️')
                ->grow(false)
                ->alignCenter(),
                


            Tables\Columns\TextColumn::make('monto')
                ->label('💰 Monto Prestado')
                ->formatStateUsing(fn ($state) => "💵 " . number_format($state, 2, ',', '.') . " US$")
                ->grow(false)
                ->alignCenter()
                ->prefix('⚙️'),
               


            Tables\Columns\TextColumn::make('fecha_inicio_pago')
                ->label('📆 Fecha de Inicio')
                ->formatStateUsing(fn ($state) => $state 
                    ? Carbon::parse($state)->translatedFormat('d \d\e F, Y') 
                    : '❌ No registrada')
                    ->grow(false)
                    ->alignCenter()
                    ->prefix('📅'),
                    
    
                

            Tables\Columns\TextColumn::make('fecha_fin')
                ->label('🛑 Fecha Final')
                ->getStateUsing(fn ($record) => $record->planPagos()->orderBy('fecha', 'desc')->first()?->fecha)
                ->formatStateUsing(fn ($state) => $state 
                    ? Carbon::parse($state)->translatedFormat('d \d\e F, Y') 
                    : '⏳ En curso')
                    ->grow(false)
                    ->alignCenter()
                    ->prefix('📅'),
                    
    
                

            Tables\Columns\BadgeColumn::make('estado')
                ->label('📊 Estado del Crédito')
                ->colors([
                    'success' => fn ($state) => $state === 'Pagado',
                    'danger' => fn ($state) => $state === 'Pendiente',
                ])
                ->formatStateUsing(fn ($state) => $state === 'Pagado' ? '🟢 Pagado' : '🔴 Pendiente')
                ->grow(false)
                ->alignCenter(),
                

        ])
        ->emptyStateHeading('❌ No tiene créditos en su historial')
        ->emptyStateDescription('Este cliente no ha solicitado ningún préstamo.')
        
            //
            ->actions([
                Tables\Actions\EditAction::make()->label('✏️ Editar'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHistorialPrestamos::route('/'),
        ];
    }
}
