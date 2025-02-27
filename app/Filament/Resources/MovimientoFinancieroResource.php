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

class MovimientoFinancieroResource extends Resource
{
    protected static ?string $model = MovimientoFinanciero::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tipo')
                    ->label('📊 Tipo de Movimiento')
                    ->options([
                        'entrada' => 'Entrada de Dinero 🟢',
                        'salida' => 'Salida de Dinero 🔴',
                        'gasto' => 'Gasto 🔴',
                    ])
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
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimientoFinancieros::route('/'),
            'create' => Pages\CreateMovimientoFinanciero::route('/create'),
        ];
    }
}
