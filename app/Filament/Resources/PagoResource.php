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

class PagoResource extends Resource
{
    protected static ?string $model = Pago::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    


    public static function form(Forms\Form $form): Forms\Form
{
    return $form
        ->schema([
            


            
            
            Select::make('prestamo_id')
            ->required()
            ->prefix('🤵')
                ->reactive()
                ->label('Escriba El Nombre Del Cliente')
                ->relationship('prestamo', 'id', fn ($query) => $query->where('estado', '!=', 'Pagado')) // Mostrar solo préstamos no pagados
                ->searchable() // Habilitar búsqueda
                ->getSearchResultsUsing(function ($search) {
                    $prestamos = Prestamo::with('cliente') // Cargar la relación cliente
                        ->where('estado', '!=', 'Pagado') // Filtrar préstamos no pagados
                        ->whereHas('cliente', function ($query) use ($search) {
                            $query->where('nombre', 'like', "%{$search}%") // Buscar por nombre del cliente
                                  ->orWhere('cedula', 'like', "%{$search}%"); // Buscar por cédula
                        })
                        ->limit(50)
                        ->get();
            
                    // Construir las opciones
                    $result = [];
                    foreach ($prestamos as $prestamo) {
                        $result[$prestamo->id] = "Cliente: {$prestamo->cliente->nombre} --- Préstamo Nº : {$prestamo->id}";
                    }
            
                    return $result;
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    $prestamo = Prestamo::with('cliente')->find($value);
            
                    return $prestamo
                        ? "Cliente: {$prestamo->cliente->nombre} - Préstamo ID: {$prestamo->id}"
                        : null;
                })
                ->afterStateUpdated(function (callable $get, callable $set) {
                    $prestamoId = $get('prestamo_id'); // Obtener el ID del préstamo seleccionado
                    if ($prestamoId) {
                        $prestamo = Prestamo::find($prestamoId); // Buscar el préstamo
                        $set('cuota_diaria', $prestamo?->cuota_diaria ?? 0); // Actualizar la cuota diaria
                    }
                })
                ->placeholder('Seleccione un préstamo'),
            






                TextInput::make('cuota_diaria')
                ->label('Valor Cuota Diaria')
                ->reactive()
                ->disabled() // Campo no editable
                ->numeric()
                ->prefix('💲')
                ->step(0.01),

    



            




            // Monto a pagar
            TextInput::make('monto')
                ->label('Monto a Pagar')
                ->numeric()
                ->required()
                ->prefix('💲')
                ->reactive()
                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                    $prestamo = Prestamo::find($get('prestamo_id'));

                    if ($prestamo && $state > $prestamo->saldo_restante) {
                        $set('monto', $prestamo->saldo_restante); // Ajustar el monto al saldo restante
                        Notification::make()
                            ->title('Error')
                            ->body('No puede pagar más del saldo restante.')
                            ->danger()
                            ->send();
                    }
                })->placeholder('Digite el monto a pagar'),

            DatePicker::make('fecha_pago')
                ->label('Fecha del Pago')
                ->prefix('🗓️')
                ->default(today())
                ->required()
                ->readonly(), // Campo no editable
                
        ]);
}

public static function table(Table $table): Table
{
    return $table
    ->query(Pago::query()->with(['prestamo', 'prestamo.cliente'])) // 🔥 Asegurar que cargamos relaciones
    ->columns([
        
        Tables\Columns\TextColumn::make('prestamo.cliente.nombre')
        ->label('📌 Cliente')
        ->formatStateUsing(fn ($state) => "<strong>🧑‍💼 $state</strong>")
        ->html() // Permite HTML para negritas
        ->sortable(),

        Tables\Columns\TextColumn::make('prestamo_id')
        ->label('📜 ID Préstamo')
        ->formatStateUsing(fn ($state) => "📜 $state") // Quitamos el fondo amarillo
        ->sortable(),

    Tables\Columns\TextColumn::make('monto')
        ->label('💰 Monto Pagado')
        ->formatStateUsing(fn ($state) => "<span style='color: #4CAF50; font-weight: bold;'>💵 " . number_format($state, 2, ',', '.') . " US$</span>")
        ->html()
        ->sortable(),

    Tables\Columns\TextColumn::make('fecha_pago')
        ->label('📅 Fecha del Pago')
        ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d \d\e F, Y'))
        ->sortable(),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPagos::route('/'),
            'create' => Pages\CreatePago::route('/create'),
            'edit' => Pages\EditPago::route('/{record}/edit'),
        ];
    }
}
