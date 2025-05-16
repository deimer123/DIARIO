<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PagoResource\Pages;
use App\Filament\Resources\PagoResource\RelationManagers;
use App\Models\Pago;
use App\Models\Cliente; // Importar el modelo Cliente
use App\Models\Prestamo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Hidden;

class PagoResource extends Resource
{
    protected static ?string $model = Pago::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()->with(['prestamo', 'prestamo.cliente', 'user']);

    if (!auth()->user()->hasRole('Administrador')) {
        $query->where('user_id', auth()->id()); // El Cobrador solo ve sus pagos
    }

    return $query;
}


    public static function form(Forms\Form $form): Forms\Form
{
    return $form
        ->schema([
            
            Hidden::make('user_id')
                ->default(fn () => auth()->id()), // Asignar el ID del usuario autenticado

            
            
                Select::make('prestamo_id')
                ->label('Escriba El Nombre Del Cliente')
                ->prefix('🤵')
                ->reactive()
                ->required()
                ->relationship('prestamo', 'id', function ($query) {
                    $query->where('estado', '!=', 'Pagado'); // Mostrar solo préstamos no pagados

                    // 🔹 Si el usuario NO es Administrador, solo ve los préstamos que él creó
                    if (!auth()->user()->hasRole('Administrador')) {
                        $query->where('user_id', auth()->id());
                    }
                })
                ->searchable()
                ->getSearchResultsUsing(function ($search) {
                    $query = Prestamo::with('cliente')->where('estado', '!=', 'Pagado');

                    if (!auth()->user()->hasRole('Administrador')) {
                        $query->where('user_id', auth()->id());
                    }

                    return $query->whereHas('cliente', function ($query) use ($search) {
                        $query->where('nombre', 'like', "%{$search}%")
                              ->orWhere('cedula', 'like', "%{$search}%");
                    })
                    ->limit(50)
                    ->get()
                    ->pluck('cliente.nombre', 'id')
                    ->map(fn ($name, $id) => "Cliente: $name - Préstamo ID: $id")
                    ->toArray();
                })
                ->placeholder('Seleccione un préstamo')
              //  ->default(fn () => str_replace(['-', '_'], [':', ' '], request()->query('cliente_nombre', ''))) // ✅ Asegura que el nombre se muestre correctamente
                ->afterStateUpdated(function (callable $get, callable $set) {
                    $prestamo = Prestamo::find($get('prestamo_id'));
                    $set('cuota_diaria', $prestamo?->cuota_diaria ?? 0);
                }),
            






                TextInput::make('cuota_diaria')
                ->label('Valor Cuota Diaria')
                ->reactive()
                ->disabled() // Campo no editable
                ->numeric()
                ->prefix('💲')
                ->suffix(fn ($state) => number_format((float) str_replace('.', '', $state), 0, ',', '.'))
                ->step(0.01),

    



            




            // Monto a pagar
            TextInput::make('monto')
    ->label('Monto a Pagar')
    ->required()
    ->numeric()
    ->reactive()

    // Mostrar en formato amigable visual
    ->suffix(fn ($state) => is_numeric($state)
        ? number_format($state, 0, ',', '.') . ' 💲'
        : null
    )

    // Limpia ceros innecesarios al cargar
    ->afterStateHydrated(function ($component, $state) {
        if (is_numeric($state)) {
            $component->state(rtrim(rtrim((string) $state, '0'), '.'));
        }
    })

    // Validación en tiempo real contra saldo restante
    ->afterStateUpdated(function (callable $get, callable $set, $state) {
        $prestamoId = $get('prestamo_id');
        if (!$prestamoId || !is_numeric($state)) return;

        $prestamo = \App\Models\Prestamo::find($prestamoId);
        if (!$prestamo) return;

        $saldoRestante = $prestamo->saldo_restante;

        if ($state > $saldoRestante) {
            $set('monto', $saldoRestante); // Corregimos el valor al máximo permitido
            \Filament\Notifications\Notification::make()
                ->title('Error')
                ->body("El monto no puede ser mayor al saldo restante: " . number_format($saldoRestante, 0, ',', '.'))
                ->danger()
                ->send();
        }
    }),



            DatePicker::make('fecha_pago')
                ->label('Fecha del Pago')
                ->prefix('🗓️')
                ->default(today())
                ->required()
                ->readonly(), // Campo no editable
                
        ]);
}


public static function table(Tables\Table $table): Tables\Table
{
    return $table
        ->paginated(false) // ✅ Desactiva la paginación (opcional)
        ->query(function () {
            $query = Pago::query(); // Asegúrate de importar el modelo Pago
        
            if (request()->has('prestamo_id')) {
                $query->where('prestamo_id', request()->query('prestamo_id'));
            }
        
            return $query;
        })
        ->columns([
            Tables\Columns\TextColumn::make('prestamo.cliente.nombre')
                ->label('📌 Cliente')
                ->grow(false)
                ->alignCenter()
                ->prefix('🧑‍💼 '),

                // 🔹 Mostrar el nombre del Cobrador SOLO si el usuario es Administrador
            Tables\Columns\TextColumn::make('user.name')
            ->label('🧑‍💼 Cobrador')
            ->prefix('👺 ')
            ->grow(false)
            ->alignCenter()
            ->toggleable()
            ->toggledHiddenByDefault(true)
            ->hidden(fn () => !auth()->user()->hasRole('Administrador')),

            Tables\Columns\TextColumn::make('prestamo_id')
                ->label('📜 ID Préstamo')
                ->prefix('#️⃣')
                ->grow(false)
               // ->toggleable()
               // ->toggledHiddenByDefault(true)
                ->alignCenter(),

            Tables\Columns\TextColumn::make('monto')
                ->label('💰 Monto Pagado')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->alignCenter(), // Alinea el texto a la derecha

            Tables\Columns\TextColumn::make('fecha_pago')
                ->label('📅 Fecha del Pago')
                ->prefix('📅 ')
                ->grow(false)
                ->alignCenter()
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d \d\e F, Y')),
                

            
        ])
            ->filters([
                Tables\Filters\Filter::make('prestamo_id')
                ->query(fn ($query) => request('prestamo_id') ? $query->where('prestamo_id', request('prestamo_id')) : $query),

            ])
            ->actions([ ]);
         //   ->headerActions([
                
         //       \Filament\Tables\Actions\CreateAction::make()
         //           ->label('Crear Pago') // 🔹 Cambia el nombre del botón
            //        ->color('success') // 🔹 Puedes cambiar el color si lo deseas
         //   ]);
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
          'list' => Pages\ListPagos::route('/'),
            'index' => Pages\CreatePago::route('/create'),
           'edit' => Pages\EditPago::route('/{record}/edit'),
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

public static function canCreate(): bool
{
    return true; // Permitir la creación de pagos para todos los usuarios
}



}
