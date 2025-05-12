<?php

namespace App\Filament\Resources;

use App\Models\MovimientoFinanciero;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\MovimientoFinancieroResource\Pages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Panel;


class MovimientoFinancieroResource extends Resource
{
    public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->breadcrumbs(false);
}


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
        ->striped() // Alterna colores en las filas
        ->paginated(false) // ✅ Desactiva la paginación
            ->columns([
                TextColumn::make('fecha')
                ->label('📅 Fecha')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d \d\e F, Y'))
                ->grow(false)
                ->alignCenter()
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->prefix('🗓️ '),

                TextColumn::make('user.name')
                ->label('🧑‍💼 Cobrador')
                ->prefix('👺')
                ->grow(false)
                ->alignCenter()
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->hidden(fn () => !auth()->user()->hasRole('Administrador')), //

                TextColumn::make('tipo')
                ->label('📊 Tipo') 
                ->grow(false)
                ->alignCenter()
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->prefix('♨️​'),

                TextColumn::make('motivo')
                ->label('📝 Motivo') 
                ->grow(false)
                ->alignCenter()                
                ->prefix('💰'),
                

                TextColumn::make('monto')
                ->label('💰 Monto')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->grow(false)
                ->alignCenter()
                ->prefix('💲'),
                

            ])
            ->actions([])
            ->headerActions([
                
                \Filament\Tables\Actions\CreateAction::make()
                    ->label('Crear Movimiento') // 🔹 Cambia el nombre del botón
                    ->color('success') // 🔹 Puedes cambiar el color si lo deseas
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
