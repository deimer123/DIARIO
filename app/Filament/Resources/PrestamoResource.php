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
                
        
    Select::make('cliente_id')// Campo para buscar al cliente por nombre
        ->label('Seleccione El Cliente')
        ->placeholder('Digite El Nombre Del Cliente')
        ->prefix('🤵')
        ->options(Cliente::pluck('nombre', 'id')->toArray() )// Carga manualmente los datos   
        ->searchable()
        ->required(),

    TextInput::make('monto')
        ->label('Monto')
        ->prefix('$')
        ->numeric()
        ->reactive()
        ->required()
        ->debounce(1000) // ⏳ Espera 500ms antes de aplicar cambios
        ->afterStateUpdated(function (callable $set, callable $get) {
            $monto = (float) str_replace('.', '', $get('monto')); // Eliminar puntos al convertir a número
            $interes = (float) $get('interes') / 100;
            $montoConInteres = $monto + ($monto * $interes);
            
            $set('saldo_restante', number_format($montoConInteres, 2, '.', ''));

            $cuotas = (int) $get('cuotas');
            if ($cuotas > 0) {
                $set('cuota_diaria', number_format($montoConInteres / $cuotas, 2, '.', ''));
            }
        })
        ->suffix(fn ($state) => number_format((float) str_replace('.', '', $state), 0, ',', '.')), // ✅ Formato correcto sin afectar la escritura
    
                
        TextInput::make('interes') // 📌 NUEVO CAMPO PARA DEFINIR EL INTERÉS
        ->label('Porcentaje de Interés (%)')
        ->prefix('%')
        ->numeric()
        ->reactive()
        ->step(0.01)
        ->required()
        ->debounce(1000)
        ->afterStateUpdated(function (callable $set, callable $get) {
            $monto = (float) str_replace('.', '', $get('monto'));
            $interes = (float) ($get('interes') ?? 0) / 100; // Evita errores con valores nulos
            $montoConInteres = $monto + ($monto * $interes);
    
            $set('saldo_restante', number_format($montoConInteres, 0, '.', ''));
    
            $cuotas = (int) ($get('cuotas') ?? 0);
            if ($cuotas > 0) {
                $set('cuota_diaria', number_format($montoConInteres / $cuotas, 0, '.', ''));
            }
        })
        ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 0, ',', '.') : '') // Muestra vacío si es null
        ->suffix(fn ($state) => $state !== null ? number_format((float) str_replace('.', '', $state), 0, ',', '.') : '')
        ->placeholder('') // Caja vacía al inicio
        ->default(null), // No pone 0 por defecto

        TextInput::make('cuotas')
        ->label('Número de Cuotas')
        ->numeric()
        ->prefix('#️⃣')
        ->required()
        ->placeholder('') // Caja vacía al inicio
        ->default(null) // Evita que se muestre 0
        ->reactive()
        ->debounce(1000)
        ->afterStateUpdated(fn (callable $set) => $set('regenerar_plan', true))
        ->rules([
            function (callable $get) {
                return function (string $attribute, $value, callable $fail) use ($get) {
                    $tipoPago = $get('tipo_pago');
                    $cuotas = (int) ($value ?? 0); // Evita errores si es null
    
                    if ($tipoPago === 'diario' && $cuotas < 24) {
                        $fail('Si el tipo de pago es diario, las cuotas deben ser mayores a 24.');
                    }
    
                    if ($tipoPago === 'semanal' && ($cuotas < 1 || $cuotas > 8)) {
                        $fail('Si el tipo de pago es semanal, las cuotas deben estar entre 1 y 8.');
                    }
    
                    if ($tipoPago === 'quincenal' && ($cuotas < 1 || $cuotas > 4)) {
                        $fail('Si el tipo de pago es quincenal, las cuotas deben estar entre 1 y 4.');
                    }
                };
            },
        ])
        ->afterStateUpdated(function (callable $set, callable $get) {
            $montoConInteres = (float) ($get('saldo_restante') ?? 0);
            $cuotas = (int) ($get('cuotas') ?? 0);
    
            if ($cuotas > 0) {
                $cuotaDiaria = $montoConInteres / $cuotas;
                $set('cuota_diaria', number_format($cuotaDiaria, 0, '.', ''));
            }
        })
        ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 0, ',', '.') : '') // Evita el 0 al inicio
        ->suffix(fn ($state) => $state !== null ? number_format((float) str_replace('.', '', $state), 0, ',', '.') : ''),

                    

    TextInput::make('saldo_restante')
        ->label('Monto Total Con Interés ($)')
        ->prefix('💲')
        ->numeric()
        ->default(0)
        ->reactive()
        ->readonly()
        ->suffix(fn ($state) => number_format((float) str_replace('.', '', $state), 0, ',', '.'))
        ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.')), // ✅ Sin decimales y con separadores,




    TextInput::make('cuota_diaria')
        ->label('Valor Cuota Diaria ($)')
        ->prefix('💲')
        ->numeric()
        ->default(0)
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
                })
                ->suffix(fn ($state) => number_format((float) str_replace('.', '', $state), 0, ',', '.'))
        ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.')),

                
    DatePicker::make('fecha_prestamo')// Fecha del Préstamo
        ->label('Fecha De Toma Del Préstamo')
        ->default(now())
        ->prefix('🗓️')
        ->required()
        ->readonly(),

        
    DatePicker::make('fecha_inicio_pago')// Fecha de Inicio de Pago
        ->label('Fecha de Inicio de Pago')
        ->required()
        ->reactive(), // Reactivo para generar el plan dinámicamente

             // Tipo de pago (Diario o Semanal)
    Select::make('tipo_pago')
        ->label('Tipo de Pago')
        ->prefix('💰')
        ->options([
            'diario' => 'Diario',
            'semanal' => 'Semanal',
            'quincenal' => 'Quincenal', // Nueva opción agregada
        ])
        ->required()
        ->reactive()
        ->afterStateUpdated(fn (callable $set) => $set('regenerar_plan', true)), // 🔄 Marca que el plan debe regenerarse



    Select::make('cobrar_domingo')
        ->label('¿Cobrar los Domingos?')
        ->options([
            'si' => 'Sí, cobrar los domingos',
            'no' => 'No, saltar los domingos'
        ])
        ->required()
        ->reactive(),

    Select::make('user_id')
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

        ->striped() // Alterna colores en las filas
        ->paginated(false) // ✅ Desactiva la paginación
        
        


        ->columns([
            Tables\Columns\TextColumn::make('cliente_info')
    ->label('😎​ Cliente/📚​ Préstamo')
    ->getStateUsing(fn (Prestamo $record) => 
        "🧑‍💼 {$record->cliente->nombre} | #️⃣ {$record->id}"
    )
    ->searchable(query: function (Builder $query, string $search) {
        return $query->whereHas('cliente', function ($query) use ($search) {
            $query->where('nombre', 'like', "%{$search}%"); // 🔍 Busca solo por el nombre del cliente
        });
    }),
    

    Tables\Columns\TextColumn::make('user.name')
                ->label('🧑‍💼 Cobrador')
                ->prefix('👺')
                ->grow(false)
                ->alignCenter()
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->hidden(fn () => !auth()->user()->hasRole('Administrador')), // Ocultar si NO es Admin

Tables\Columns\TextColumn::make('monto')
    ->label('💵 Monto')
                ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->alignCenter(), // Alinea el texto a la derecha
    

                    Tables\Columns\TextColumn::make('saldo_restante')
                    ->label(' 💱​ Restante')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                    ->prefix('💲')
                    ->grow(false)
                    ->alignCenter(), // Alinea el texto a la derecha


Tables\Columns\TextColumn::make('saldo_pagado')
    ->label(' ✔️​ Pagado')
    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')) // Formato con separadores
                ->prefix('💲')
                ->grow(false)
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->alignCenter(), // Alinea el texto a la derecha
    

Tables\Columns\TextColumn::make('cuotas_pagadas')
    ->label(' 🗂️​ Cuotas')
    ->hidden(fn () => !session('mostrarColumnas', false))
    ->getStateUsing(fn (Prestamo $record) => 
        "📊 Total: {$record->total_cuotas} | ✅ Pagadas: {$record->cuotas_pagadas}"
)
->grow(false)
                ->alignCenter(), // Alinea el texto a la derecha
   
    
    

               

            Tables\Columns\BadgeColumn::make('estado')
                ->label(' 💯​ Estado')
                ->grow(false)
                ->alignCenter() // Alinea el texto a la derecha
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->colors([
                    'success' => 'Pagado', // Verde para Pagado
                    'danger' => 'Pendiente', // Rojo para Pendiente
                ])
                ->formatStateUsing(function (string $state): string {
                    // Opcional: Formatear el texto del estado
                    return ucfirst($state); // Convertir a "Pagado" o "Pendiente"
                }),

                
                
        ])
            ->filters([
                //
            ])
            


            

            ->actions([

              //  Tables\Actions\ViewAction::make(), // 👀 Permitir solo ver detalles
                Tables\Actions\Action::make('Ver Plan de Pagos')
        ->icon('heroicon-o-calendar')
        ->url(fn (Prestamo $record) => PlanPagoResource::getUrl('index', ['prestamo_id' => $record->id])),
        
        
       // ->openUrlInNewTab(), // Opcional: abre la página en una nueva pestaña
            ])             

            



            ->headerActions([
                
                \Filament\Tables\Actions\CreateAction::make()
                    ->label('Crear Prestamo') // 🔹 Cambia el nombre del botón
                    ->color('success') // 🔹 Puedes cambiar el color si lo deseas
            ]);
    }

    




    public static function generarPlanDePago(string $fechaInicio, int $cuotas, string $tipoPago, string $cobrarDomingo = 'no'): array
{
    $fecha = \Carbon\Carbon::parse($fechaInicio);
    $intervalo = match ($tipoPago) {
        'diario' => 1,       // Intervalo de 1 día
        'semanal' => 7,      // Intervalo de 7 días
        'quincenal' => 15,   // Intervalo de 15 días (quincena)
        default => 1,        // Por defecto, diario
    };

    $plan = [];

    for ($i = 0; $i < $cuotas; $i++) {
        // 📌 Si el usuario selecciona "No" en "cobrar_domingo", los domingos se saltan
        if ($tipoPago === 'diario' && $cobrarDomingo === 'no') {
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
        'view' => Pages\ViewPrestamo::route('/{record}'), // 👈 Agregar la ruta de vista
        
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