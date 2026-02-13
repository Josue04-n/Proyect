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
        //
        Relation::enforceMorphMap([
            'App\Models\PagoOperario' => 'App\Models\PagoOperario',
            'App\Models\Venta' => 'App\Models\Venta',
        ]);

        Venta::observe(VentaObserver::class);
    }
}
