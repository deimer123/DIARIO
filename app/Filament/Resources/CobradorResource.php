<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use App\Filament\Resources\CobradorResource\Pages;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\CreateAction;


class CobradorResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $pluralLabel = 'Cobradores';

    public static function getNavigationLabel(): string
{
    return 'Cobradores'; // 📝 Cambia el nombre que aparece en el menú
}


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->prefix('👺 ')
                    ->label('Nombre'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->prefix('📫 ')
                    ->label('Correo electrónico'),
                TextInput::make('password')
                    ->password()
                    ->prefix('💀 ')
                    ->label('Contraseña')
                    ->required(fn ($livewire) => $livewire instanceof CreateRecord) // Requerido solo en creación
                    ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null) // Encripta la contraseña
                    ->hiddenOn('edit'),
                    Select::make('role')
    ->label('Rol')
    ->options(Role::where('name', '!=', 'Administrador')->pluck('name', 'name')->toArray()) // 🔹 Excluye "Administrador"
    ->default('Cobrador')
    ->required()
    ->prefix('🧌 ')
    ->dehydrateStateUsing(fn ($state) => $state) // 🔹 Guarda el valor correctamente
    ->afterStateUpdated(fn ($state, $set) => $set('role', $state))
    ->saveRelationshipsUsing(fn ($state, $record) => $record->assignRole($state)), // 🔹 Asegura que se guarde el estado
                    
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
        ->striped() // Alterna colores en las filas
        ->paginated(false) // ✅ Desactiva la paginación

        ->query(
            User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Administrador');
            })
        )
            ->columns([
                TextColumn::make('name')
                ->label('🤵 Nombre')
                ->prefix('👺 ')
                ->grow(false)
                ->alignCenter(),
                TextColumn::make('email')
                ->label('🕸️ Correo')
                ->prefix('📫 ')
                ->grow(false)
                ->alignCenter(),
                
            ])
            ->filters([])
            ->actions([])
            ->headerActions([
                \Filament\Tables\Actions\CreateAction::make()
                    ->label('Crear Cobrador') // 🔹 Cambia el nombre del botón
                    ->color('success') // 🔹 Puedes cambiar el color si lo deseas
            ]);
             

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCobradors::route('/'),
            'create' => Pages\CreateCobrador::route('/create'),
            'edit' => Pages\EditCobrador::route('/{record}/edit'),
        ];
    }

    /**
     * 🔹 Solo los administradores pueden acceder a este recurso.
     */
    
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
  
   
    


protected function mutateFormDataBeforeCreate(array $data): array
{
    
    $data['password'] = bcrypt($data['password']); // 🔒 Encripta la contraseña

    return $data;
}
}

