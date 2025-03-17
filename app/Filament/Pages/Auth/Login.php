<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function getTitle(): string
    {
        return 'Invesiones Credi-Ya'; // Cambia el nombre de Laravel por el tuyo
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->required(),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(),
            ]);
    }
}
