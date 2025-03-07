<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrestamoResource\Pages;
use App\Models\Prestamo;
use App\Models\Cliente; // Importar el modelo Cliente
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Builder;




class PrestamoResource extends Resource
{

    
    protected static ?string $model = Prestamo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function query(Builder $query): Builder 
    {
    $query->withCount([
        'planPagos as total_cuotas', // Cuenta TODAS las cuotas
        'planPagos as cuotas_pagadas' => function ($query) {
            $query->where('estado', 'Pagado'); 
        }
    ]);}

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()->hasRole('Administrador')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([

                         Hidden::make('user_id')
                         ->default(auth()->id()), // Asigna el usuario autenticado automáticamente
                // Campo para buscar al cliente por nombre
                        Select::make('cliente_id')
                        ->label('Seleccione El Cliente')
                        ->placeholder('Digite El Nombre Del Cliente')
                        ->prefix('🤵')
                        ->options(Cliente::pluck('nombre', 'id')->toArray() )// Carga manualmente los datos   
                        ->searchable()
                        ->required(),

                        TextInput::make('monto')
                        ->prefix('💲')
                        ->numeric()
                        ->placeholder('Digite El Monto A Prestar')
                        ->step(0.01)
                        ->required()
                        ->reactive() // Reactivo para recalcular valores
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            // Al cambiar el monto, recalcula el monto con interés
                            $monto = (float)$get('monto');
                            $interes = $monto * 1.20; // Aplica el 20% de interés
                            $set('saldo_restante', number_format($interes, 2, '.', ''));

                            // Actualiza la cuota diaria si ya hay cuotas definidas
                            $cuotas = (int)$get('cuotas');
                            if ($cuotas > 0) {
                                $cuotaDiaria = $interes / $cuotas;
                                $set('cuota_diaria', number_format($cuotaDiaria, 2, '.', ''));
                            }
                        }),
                        
                                ///OJO REVIZAR AQUI


                        // Número de Cuotas
                        Forms\Components\TextInput::make('cuotas')
                        ->label('Número de Cuotas')
                        ->numeric()
                        ->prefix('#️⃣')
                        ->required()
                        ->reactive()
                        ->rules([
                            function (callable $get) {
                                return function (string $attribute, $value, callable $fail) use ($get) {
                                    $tipoPago = $get('tipo_pago');
                                    $cuotas = (int)$value;
                    
                                    if ($tipoPago === 'diario' && $cuotas < 24) {
                                        $fail('Si el tipo de pago es diario, las cuotas deben ser mayores a 24.');
                                    }
                    
                                    if ($tipoPago === 'semanal' && ($cuotas < 1 || $cuotas > 4)) {
                                        $fail('Si el tipo de pago es semanal, las cuotas deben estar entre 1 y 4.');
                                    }
                                };
                            },
                        ])
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            $montoConInteres = (float)$get('saldo_restante');
                            $cuotas = (int)$get('cuotas');
                    
                            if ($cuotas > 0) {
                                $cuotaDiaria = $montoConInteres / $cuotas;
                                $set('cuota_diaria', number_format($cuotaDiaria, 2, '.', ''));
                            }
                        }),
                    

                    

                TextInput::make('saldo_restante')
    ->label('Monto Total Con Interés ($)')
    ->prefix('💲')
    ->numeric()
    ->default(0.00)
    ->reactive()
    ->readonly()
    ->afterStateUpdated(function (callable $get, callable $set) {
        $monto = (float)$get('monto');
        $interes = $monto * 1.20; // 20% de interés
        $set('saldo_restante', number_format($interes, 2, '.', ''));
    }),




    TextInput::make('cuota_diaria')
    ->label('Valor Cuota Diaria ($)')
    ->prefix('💲')
    ->numeric()
    ->default(0.00)
    ->reactive()
    ->readonly()
    ->afterStateUpdated(function (callable $get, callable $set) {
        $montoConInteres = (float)$get('saldo_restante');
        $cuotas = (int)$get('cuotas');

        if ($cuotas > 0) {
            $cuotaDiaria = $montoConInteres / $cuotas;
            $set('cuota_diaria', number_format($cuotaDiaria, 2, '.', ''));
        } else {
            $set('cuota_diaria', 0);
        }
    }),

                // Fecha del Préstamo
            DatePicker::make('fecha_prestamo')
            ->label('Fecha De Toma Del Préstamo')
            ->default(now())
            ->prefix('🗓️')
            ->required()
            ->readonly(),

        // Fecha de Inicio de Pago
        Forms\Components\DatePicker::make('fecha_inicio_pago')
        ->label('Fecha de Inicio de Pago')
        ->required()
        ->reactive(), // Reactivo para generar el plan dinámicamente

             // Tipo de pago (Diario o Semanal)
             Forms\Components\Select::make('tipo_pago')
             ->label('Tipo de Pago')
             ->prefix('💰')
             ->options([
                 'diario' => 'Diario',
                 'semanal' => 'Semanal',
             ])
             ->required()
             ->reactive(),

             Forms\Components\Select::make('user_id')
    ->label('Asignar Cobrador')
    ->relationship('user', 'name') // Relación con el modelo User
    ->searchable()
    ->preload()
    ->hidden(fn () => !auth()->user()->hasRole('Administrador')) // Solo el Admin lo ve
    ->required(fn () => auth()->user()->hasRole('Administrador')), // Obligatorio para el Admin
             

             

            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table

        ->striped() // Agrega filas con colores alternos para mejorar la lectura
        
        


        ->columns([
            Tables\Columns\TextColumn::make('cliente_info')
    ->label(' 😎​ Cliente / 📚​ Préstamo')
    ->getStateUsing(fn (Prestamo $record) => 
        "🧑‍💼 {$record->cliente->nombre} | #️⃣ {$record->id}"
    )
    ->extraAttributes([
        'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
    ]),

    Tables\Columns\TextColumn::make('user.name')
                ->label('🧑‍💼 Cobrador')
                ->sortable()
                ->wrap() // Hace que el texto no se desborde
                ->hidden(fn () => !auth()->user()->hasRole('Administrador')), // Ocultar si NO es Admin

Tables\Columns\TextColumn::make('monto')
    ->label('💵 Monto')
    ->prefix('💵')
    ->money('USD')
    ->wrap() // Hace que el texto no se desborde
    ->extraAttributes([
        'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
    ]),
    

Tables\Columns\TextColumn::make('saldo_restante')
    ->label(' 💱​ Restante')
    ->prefix('💰➖')
    ->money('USD')
    ->wrap() // Hace que el texto no se desborde
    ->extraAttributes([
        'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
    ]),


Tables\Columns\TextColumn::make('saldo_pagado')
    ->label(' ✔️​ Pagado')
    ->prefix('💰➕')
    ->money('USD')
    ->wrap() // Hace que el texto no se desborde
    ->extraAttributes([
        'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
    ]),
    

Tables\Columns\TextColumn::make('cuotas_pagadas')
    ->label(' 🗂️​ Cuotas')
    ->getStateUsing(fn (Prestamo $record) => 
        "📊 Total: {$record->total_cuotas} | ✅ Pagadas: {$record->cuotas_pagadas}"
    )
    ->wrap() // Hace que el texto no se desborde
    ->extraAttributes([
        'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
    ]),
    

               

            Tables\Columns\BadgeColumn::make('estado')
                ->label(' 💯​ Estado')
                ->colors([
                    'success' => 'Pagado', // Verde para Pagado
                    'danger' => 'Pendiente', // Rojo para Pendiente
                ])
                ->formatStateUsing(function (string $state): string {
                    // Opcional: Formatear el texto del estado
                    return ucfirst($state); // Convertir a "Pagado" o "Pendiente"
                })
                ->wrap() // Hace que el texto no se desborde
                ->extraAttributes([
                    'class' => 'border-2 border-gray-700 p-4 text-left text-lg font-semibold', // 🔹 Bordes gruesos y alineación a la izquierda
                ]),
        ])
            ->filters([
                //
            ])
            


            

            ->actions([
                Tables\Actions\Action::make('Ver Plan de Pagos')
        ->icon('heroicon-o-calendar')
        ->url(fn (Prestamo $record) => PlanPagoResource::getUrl('index', ['prestamo_id' => $record->id])),
        
        
       // ->openUrlInNewTab(), // Opcional: abre la página en una nueva pestaña
            ])             

            



            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    




    public static function generarPlanDePago(string $fechaInicio, int $cuotas, string $tipoPago): array
{
    $fecha = \Carbon\Carbon::parse($fechaInicio);
    $intervalo = $tipoPago === 'diario' ? 1 : 7; // 1 día para diario, 7 días para semanal
    $plan = [];

    for ($i = 0; $i < $cuotas; $i++) {
        // Saltar domingos si es un pago diario
        if ($tipoPago === 'diario') {
            while ($fecha->isSunday()) {
                $fecha->addDay();
            }
        }

        $plan[] = [
            'fecha' => $fecha->toDateString(),
            'estado' => 'Pendiente',
        ];

        $fecha->addDays($intervalo);
    }

    return $plan;
}





    

public static function getPages(): array
{
    return [
        'index' => Pages\ListPrestamos::route('/'),
        'create' => Pages\CreatePrestamo::route('/create'),
        'edit' => Pages\EditPrestamo::route('/{record}/edit'),
        
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
