<?php

namespace App\Providers;

use App\Models\Program;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        // Programas del motor: {engineProgram} acepta slug o id numérico (solo engine_enabled).
        // Va acá y no en routes/api.php: con route:cache los archivos de rutas no se cargan
        // y el binding se perdía (404 "No query results for model [Program] work-travel").
        Route::bind('engineProgram', function (string $value) {
            return Program::query()
                ->where('engine_enabled', true)
                ->where(fn ($q) => $q->where('slug', $value)->when(is_numeric($value), fn ($q) => $q->orWhere('id', (int) $value)))
                ->firstOrFail();
        });
    }
}
