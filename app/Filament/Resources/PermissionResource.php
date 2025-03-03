<?php

namespace App\Filament\Resources;

use Spatie\Permission\Models\Permission;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\PermissionResource\Pages;
use Illuminate\Database\Eloquent\Model; // ✅ Importa Model correctamente

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
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
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    // ✅ Cambia el tipo del parámetro de Spatie\Permission\Models\Permission a Model
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function canEdit(Model $record): bool // 🔥 Corregido
    {
        return auth()->user()->hasRole('Administrador');
    }

    public static function canDelete(Model $record): bool // 🔥 Corregido
    {
        return auth()->user()->hasRole('Administrador');
    }
}
