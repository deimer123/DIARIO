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

class CobradorResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('Nombre'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->label('Correo electrónico'),
                TextInput::make('password')
                    ->password()
                    ->label('Contraseña')
                    ->required(fn ($livewire) => $livewire instanceof CreateRecord) // Requerido solo en creación
                    ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null) // Encripta la contraseña
                    ->hiddenOn('edit'),
                    Select::make('role')
    ->label('Rol')
    ->options(Role::pluck('name', 'name')->toArray())
    ->default('Cobrador')
    ->required()
    ->live() // 🔹 Hace que el estado del campo se actualice en tiempo real
    ->dehydrateStateUsing(fn ($state) => $state),
                    
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre'),
                TextColumn::make('email')->label('Correo'),
                TextColumn::make('roles.name')->label('Rol'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
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
        return auth()->check() && auth()->user()->hasRole('Administrador');
    }

    /**
     * 🔹 Se ejecuta después de crear un usuario
     */
    public static function afterCreate($record, array $data)
{
    dd(request()->all());

    if (isset($data['role'])) {
        $record->syncRoles([$data['role']]); // Asigna el rol seleccionado
    } else {
        throw new \Exception("No se seleccionó ningún rol.");
    }

    
}


protected function mutateFormDataBeforeCreate(array $data): array
{
    
    $data['password'] = bcrypt($data['password']); // 🔒 Encripta la contraseña

    return $data;
}
}

