<?php

namespace App\Filament\Resources;

use App\Models\MovimientoFinanciero;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\MovimientoFinancieroResource\Pages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;


class MovimientoFinancieroResource extends Resource
{
    protected static ?string $model = MovimientoFinanciero::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()->hasRole('Administrador')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tipo')
                    ->label('📊 Tipo de Movimiento')
                    ->options(self::getOpcionesMovimiento())
                ->required(),

                TextInput::make('monto')
                    ->label('💰 Monto')
                    ->numeric()
                    ->required(),

                TextInput::make('motivo')
                    ->label('📝 Motivo')
                    ->required(),

                DatePicker::make('fecha')
                    ->label('📅 Fecha')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')->label('📅 Fecha')->sortable()->prefix('🗓️  ')->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),

                TextColumn::make('user.name')
                ->label('🧑‍💼 Cobrador')
                ->sortable()
                ->hidden(fn () => !auth()->user()->hasRole('Administrador')), //

                TextColumn::make('tipo')->label('📊 Tipo') ->prefix('♨️​')->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),

                TextColumn::make('motivo')->label('📝 Motivo') ->prefix('💰')->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),

                TextColumn::make('monto')->label('💰 Monto')->sortable()->prefix('💲')->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),

            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Administrador')), // ✅ Solo admins pueden ver "Editar"
    
                DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Administrador')), // ✅ Solo admins pueden ver "Eliminar"
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Administrador')), // ✅ Solo admins pueden eliminar en masa
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimientoFinancieros::route('/'),
            'create' => Pages\CreateMovimientoFinanciero::route('/create'),
        ];
    }

    public static function canEdit(Model $record): bool
{
    return auth()->user()->hasRole('Administrador');
}
public static function canDelete(Model $record): bool
{
    return auth()->user()->hasRole('Administrador');
}

protected static function getOpcionesMovimiento(): array
{
    if (auth()->user()->hasRole('Administrador')) {
        return [
            'entrada' => 'Entrada de Dinero 🟢',
            'salida' => 'Salida de Dinero 🔴',
            'gasto' => 'Gasto 🔴',
        ];
    }

    return [
        'gasto' => 'Gasto 🔴',
    ];
}

public static function query(Builder $query): Builder
{
    if (auth()->user()->hasRole('Administrador')) {
        return $query; // Ver todos los movimientos
    }

    return $query->where('user_id', auth()->id()); // Solo ver los suyos
}




}
