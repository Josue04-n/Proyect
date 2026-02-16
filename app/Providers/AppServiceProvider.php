<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Venta;
use App\Observers\VentaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
public function boot(): void
{
    Relation::enforceMorphMap([
        'user'          => 'App\Models\User',
        'pago_operario' => 'App\Models\PagoOperario',
        'venta'         => 'App\Models\Venta',
        // Agrega aquí cualquier otro modelo que use relaciones polimórficas
    ]);

    Venta::observe(VentaObserver::class);
}

    
}
