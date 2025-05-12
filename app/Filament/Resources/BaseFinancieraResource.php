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
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;


class BaseFinancieraResource extends Resource
{
    
    protected static ?string $model = BaseFinanciera::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Base Financiera';
    protected static ?string $pluralLabel = 'Base Financiera';
    protected static ?string $slug = 'base-financiera';
    
    
   
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->striped() // Alterna colores en las filas
        ->paginated(false) // ✅ Desactiva la paginación
            ->columns([
                

            ])
            ->actions([]);
            
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBaseFinancieras::route('/'),
           // 'create' => Pages\CreateBaseFinanciera::route('/create'),
          //  'edit' => Pages\EditBaseFinanciera::route('/{record}/edit'),
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


