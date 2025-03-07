<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanPagoResource\Pages;
use App\Models\PlanPago;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Carbon\Carbon;


class PlanPagoResource extends Resource
{
    protected static ?string $model = PlanPago::class;


    public static function canCreate(): bool
    {
        return false; // Esto oculta el botón "Crear plan pago"
    }

    public static function shouldRegisterNavigation(): bool
{
    return false; // Oculta el recurso del menú lateral
}

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('prestamo_id')
                    ->relationship('prestamo', 'id')
                    ->label('Préstamo')
                    ->required(),

                Forms\Components\DatePicker::make('fecha')
                    ->label('Fecha de Pago')
                    ->required(),

                Forms\Components\Select::make('estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Pagado' => 'Pagado',
                    ])
                    ->label('Estado')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
        ->paginated(false) // ✅ Desactiva la paginación

        ->query(function () {
            $query = PlanPago::query();
        
            if (request()->has('prestamo_id')) {
                $query->where('prestamo_id', request()->query('prestamo_id'));
            }
        
            return $query;
        })
            ->columns([
                

                    Tables\Columns\TextColumn::make('cuota')
    ->label(' 🗃️​ Cuota')
    ->prefix('🔢')
    ->getStateUsing(fn($record) => 'Cuota ' . ($record->prestamo->planPagos->search(fn($pago) => $pago->id === $record->id) + 1))
    ->sortable(),
                
    Tables\Columns\TextColumn::make('fecha')
    ->label('📅 Fecha')
    ->sortable()
    ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d \d\e F, Y')),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label(' 💹​ Estado')
                    ->colors([
                        'danger' => 'Pendiente',
                        'success' => 'Pagado',
                    ]),
            ])
            ->filters([
               
                    Tables\Filters\Filter::make('prestamo_id')
                ->query(fn ($query) => request('prestamo_id') ? $query->where('prestamo_id', request('prestamo_id')) : $query),


            ])
            ->actions([
             //   Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanPagos::route('/'),
           //'edit' => Pages\EditPlanPago::route('/{record}/edit'),
        ];
    }
}


