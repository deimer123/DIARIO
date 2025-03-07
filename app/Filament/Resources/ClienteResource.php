<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Button;
use Illuminate\Database\Eloquent\Model;


class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                ->placeholder('Nombre Completo')
                ->prefix('🤵')
                ->required(),
                Forms\Components\TextInput::make('cedula')
                ->placeholder('Identificacion')
                ->prefix('🪪')
                ->required()
                ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('telefono')
                ->placeholder('Celular')
                ->unique(ignoreRecord: true)
                ->prefix('📲')
                ->required(),
                Forms\Components\TextInput::make('direccion')
                ->placeholder('Direccion o lugar de referencia')
                ->prefix('🏠')
                ->required(),
                Forms\Components\FileUpload::make('foto')
    ->image()
    ->label('Foto Referencia')
    ->required()
    ->enableDownload()
    ->enableOpen()
//->disk('public') // Usar almacenamiento público
    ->directory('uploads/fotos') // Carpeta dentro de storage/app/public
    ->visibility('public'), // Asegura que la imagen sea accesible
 //   ->live(), // Eliminar el `false` para permitir la carga en tiempo real

                




            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                ->label('🎥​ Foto')
                ->url(fn ($record) => asset('storage/' . $record->foto))
                ->circular()
                ->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),


            // Opcional: muestra imágenes redondas
                Tables\Columns\TextColumn::make('nombre')->sortable()
                ->label('✍️​ Nombre')->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),


           
                Tables\Columns\TextColumn::make('cedula')->label('​📰​​ Cedula')->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),


           
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('historial_prestamos')
                ->label('📜 Ver Historial')
                ->icon('heroicon-o-document-text')
                ->url(fn ($record) => HistorialPrestamosResource::getUrl('index', ['cliente_id' => $record->id])), // 🔥 REDIRIGE AL HISTORIAL DEL CLIENTE
                
            
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
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
public static function canDeleteAny(): bool
{
    return auth()->user()->hasRole('Administrador'); // Solo Admin puede ver el botón eliminar
}
}
