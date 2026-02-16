<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
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
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString; 

use App\Filament\Widgets\VentasSucursalChart;
use App\Filament\Widgets\TopClientesChart;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $nombreEmpresa = 'Sistema Producción';
        $brandContent = $nombreEmpresa; 
        try {
            if (Schema::hasTable('configuraciones')) {
                $config = Configuracion::first();
                
                if ($config) {
                    $nombreEmpresa = $config->nombre_comercial ?? 'Sistema Producción';
                    
                    if ($config->logo) {
                        $logoUrl = asset('storage/' . $config->logo);
                        
                        $brandContent = new HtmlString("
                            <div class='flex items-center gap-3'>
                                <img src='{$logoUrl}' alt='Logo' style='height: 2.5rem;' class='object-contain' />
                                <span class='font-bold text-lg hidden md:block'>{$nombreEmpresa}</span>
                            </div>
                        ");
                    } else {
                        $brandContent = $nombreEmpresa;
                    }
                }
            }
        } catch (\Exception $e) {
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            
            ->brandName($nombreEmpresa) 
            ->brandLogo($brandContent)  
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,
                VentasSucursalChart::class,
                TopClientesChart::class,
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
}