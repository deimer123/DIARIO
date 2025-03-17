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
use Illuminate\Support\Facades\Storage;


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
    //->required()
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
        
        
        ->paginated(false) // ✅ Desactiva la paginación
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                ->label('🎥 Foto')
                ->getStateUsing(fn ($record) => Storage::url($record->foto)) // ✅ Evita duplicar la ruta
                ->url(fn ($record) => Storage::url($record->foto)) // ✅ Evita duplicar la ruta
                ->circular()
                ->size(40)
                ->alignCenter(),
                

            // Opcional: muestra imágenes redondas
                Tables\Columns\TextColumn::make('nombre')
                ->label('✍️​ Nombre')
                ->prefix('🧑‍💼')
                ->searchable()
                ->grow(false)
                ->alignCenter(), 

           
                Tables\Columns\TextColumn::make('cedula')
                ->label('​📰​​ Cedula')
                ->prefix('🆔')
                ->grow(false)
                ->searchable()
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->alignCenter(), 


           
            ])
            ->filters([
                //
            ])
            ->actions([



                
                

                Tables\Actions\Action::make('historial_prestamos')
                ->label('📜 Ver Historial')
                ->icon('heroicon-o-document-text')
                ->url(fn ($record) => HistorialPrestamosResource::getUrl('index', ['cliente_id' => $record->id])), // 🔥 REDIRIGE AL HISTORIAL DEL CLIENTE
                
                
            
            ])

            

            ->headerActions([
                

                    \Filament\Tables\Actions\CreateAction::make()
                    ->label('Crear Cliente') // 🔹 Cambia el nombre del botón
                    ->color('success') // 🔹 Puedes cambiar el color si lo deseas
            ])


            ->bulkActions([]);
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
