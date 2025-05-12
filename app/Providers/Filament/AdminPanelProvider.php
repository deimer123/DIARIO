<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Enums\ThemeMode;
use Filament\Navigation\NavigationGroup;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::registerStyles([
            asset('css/custom.css'), // Asegúrate de que el archivo esté en `public/css/`
        ]);

        Filament::serving(function () {
            Filament::registerWidgets([
                PrestamosEnRango::class,
            ]);
        });
    }
}


class AdminPanelProvider extends PanelProvider
{


    


    public function header(): ?View
{
    return view('filament.header', [
        'logo' => asset('storage/logo.png'), // Asegúrate de que la imagen esté en storage
        'width' => '50px', // Ajusta el ancho aquí
        'height' => 'auto', // Mantiene la proporción
    ]);
}
    



    public function panel(Panel $panel): Panel
    {
            

        
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login() // Aquí indicamos la nueva página de login
            ->brandName('Inversiones Credi-Ya -- Dinero al Instante') // Cambia Laravel por el nombre de tu empresa
            ->brandLogo(asset('storage/logo.png')) // Cambia el logo en la barra lateral
            ->brandLogoHeight('60px')
            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->authGuard('web') // 🔹 Asegura que Filament use el guard 'web'
            ->colors([
                'primary' => Color::Green,
            ])
            
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages($this->getPages()) // ⬅ Aquí se usa la función `getPages()`
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
              //  Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class, // ⬅ Opción: puedes comentar esto para quitar la tarjeta de info
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Definir las páginas disponibles en el panel
     */
    protected function getPages(): array
    {
        return [
          \App\Filament\Pages\CustomDashboard::class, // Agrega tu dashboard personalizado
        ];
    }
}
