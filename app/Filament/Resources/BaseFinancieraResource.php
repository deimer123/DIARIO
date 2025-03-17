<?php


namespace App\Filament\Resources;

use App\Models\BaseFinanciera;
use App\Models\Prestamo;
use App\Models\MovimientoFinanciero;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Card;
use App\Filament\Resources\BaseFinancieraResource\Pages;
use Illuminate\Database\Eloquent\Model;


class BaseFinancieraResource extends Resource
{
    
    protected static ?string $model = BaseFinanciera::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Base Financiera';
    protected static ?string $pluralLabel = 'Base Financiera';
    protected static ?string $slug = 'base-financiera';
    
    
    
    
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('base_inicial')
                            ->label('🏦 Base Inicial de Préstamos')
                            ->numeric()
                            ->required()
                            ->default(fn () => BaseFinanciera::obtenerBase()->base_inicial)
                            ->extraAttributes(['class' => 'border border-gray-300 p-2 rounded']), // 🔹 Borde y espaciado
    
                        TextInput::make('monto_disponible')
                            ->label('💰 Monto Disponible')
                            ->numeric()
                            ->default(fn () => BaseFinanciera::obtenerBase()->monto_disponible)
                            ->extraAttributes(['class' => 'border border-gray-300 p-2 rounded']),
    
                        TextInput::make('total_prestado')
                            ->label('💸 Total Prestado')
                            ->numeric()
                            ->disabled()
                            ->default(fn () => BaseFinanciera::obtenerBase()->total_prestado)
                            ->extraAttributes(['class' => 'border border-gray-300 p-2 rounded']),
    
                        TextInput::make('total_pendiente')
                            ->label('🔴 Pendiente por Cobrar')
                            ->numeric()
                            ->disabled()
                            ->default(fn () => BaseFinanciera::obtenerBase()->total_pendiente)
                            ->extraAttributes(['class' => 'border border-gray-300 p-2 rounded']),
    
                        TextInput::make('total_gastos_salidas')
                            ->label('💸 Total Gastos y Salidas')
                            ->numeric()
                            ->disabled()
                            ->default(fn () => BaseFinanciera::obtenerBase()->total_gastos_salidas)
                            ->extraAttributes(['class' => 'border border-gray-300 p-2 rounded']),
    
                        TextInput::make('balance_ajustado')
                            ->label('📊 Balance Ajustado')
                            ->numeric()
                            ->disabled()
                            ->default(fn () => BaseFinanciera::calcularBalanceAjustado())
                            ->extraAttributes(['class' => 'border border-gray-300 p-2 rounded']),
    
                        TextInput::make('ganancia')
                            ->label('📈 Ganancia Total')
                            ->numeric()
                            ->disabled()
                            ->default(fn () => BaseFinanciera::obtenerBase()->ganancia)
                            ->extraAttributes(['class' => 'border border-gray-300 p-2 rounded']),
                    ])
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->striped() // Alterna colores en las filas
        ->paginated(false) // ✅ Desactiva la paginación
        ->columns([
            TextColumn::make('base_inicial')
                ->label('🏦 Base Inicial')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->alignCenter(), // Alinea el texto a la derecha
                
                
           

            TextColumn::make('monto_disponible')
                ->label('💰 Disponible')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->alignCenter(), // Alinea el texto a la derecha
           

            TextColumn::make('total_prestado')
                ->label('💸 Prestado')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->alignCenter(), // Alinea el texto a la derecha

           

            TextColumn::make('total_pendiente')
                ->label('🔴 Cobrar')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->alignCenter(), // Alinea el texto a la derecha

           

            TextColumn::make('total_gastos_salidas')
                ->label('💸 Gastos')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->alignCenter(), // Alinea el texto a la derecha

           

           
            TextColumn::make('ganancia')
                ->label('📈 Ganancia')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->alignCenter(), // Alinea el texto a la derecha

           
        ])
        
        
            ->actions([])


            ->headerActions([
                
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBaseFinancieras::route('/'),
            'create' => Pages\CreateBaseFinanciera::route('/create'),
            'edit' => Pages\EditBaseFinanciera::route('/{record}/edit'),
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
