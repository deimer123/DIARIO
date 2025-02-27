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
                ->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),

                

            Tables\Columns\TextColumn::make('cliente.nombre')
                ->label('🧑‍💼 Cliente')
                ->prefix('🗣️')
                ->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),


            Tables\Columns\TextColumn::make('monto')
                ->label('💰 Monto Prestado')
                ->formatStateUsing(fn ($state) => "💵 " . number_format($state, 2, ',', '.') . " US$")
                ->prefix('⚙️')
                ->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),


            Tables\Columns\TextColumn::make('fecha_inicio_pago')
                ->label('📆 Fecha de Inicio')
                ->formatStateUsing(fn ($state) => $state 
                    ? Carbon::parse($state)->translatedFormat('d \d\e F, Y') 
                    : '❌ No registrada')
                    ->prefix('📅')
                    ->extraAttributes([
                        'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                    ]),
    
                

            Tables\Columns\TextColumn::make('fecha_fin')
                ->label('🛑 Fecha Final')
                ->getStateUsing(fn ($record) => $record->planPagos()->orderBy('fecha', 'desc')->first()?->fecha)
                ->formatStateUsing(fn ($state) => $state 
                    ? Carbon::parse($state)->translatedFormat('d \d\e F, Y') 
                    : '⏳ En curso')
                    ->prefix('📅')
                    ->extraAttributes([
                        'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                    ]),
    
                

            Tables\Columns\BadgeColumn::make('estado')
                ->label('📊 Estado del Crédito')
                ->colors([
                    'success' => fn ($state) => $state === 'Pagado',
                    'danger' => fn ($state) => $state === 'Pendiente',
                ])
                ->formatStateUsing(fn ($state) => $state === 'Pagado' ? '🟢 Pagado' : '🔴 Pendiente')
                ->sortable()
                ->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),

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
