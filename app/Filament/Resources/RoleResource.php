<?php

namespace App\Filament\Resources;

use Spatie\Permission\Models\Role;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Resources\RoleResource\Pages;
use Illuminate\Database\Eloquent\Model; // ✅ Importar Model correctamente

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    public static function shouldRegisterNavigation(): bool
{
    return false; // Oculta el recurso del menú lateral
}

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true),
                CheckboxList::make('permissions')
                    ->relationship('permissions', 'name')
                    ->columns(2)
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                BadgeColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    // ✅ Corregir funciones de permisos
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function canEdit(Model $record): bool // 🔥 Cambio de Role a Model
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function canDelete(Model $record): bool // 🔥 Cambio de Role a Model
    {
        return auth()->user()->hasRole('Administrador');
    }
}
