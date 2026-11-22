<?php

namespace App\Providers;

use App\Models\Program;
use App\Models\SystemSetting;
use DateTimeZone;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Throwable;

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

        $this->applyConfiguredTimezone();

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

    /**
     * Zona horaria elegida en Configuración (system_settings.app_timezone). Sin valor guardado
     * —o sin base de datos todavía (instalación, migraciones)— queda la de config/app.php.
     */
    private function applyConfiguredTimezone(): void
    {
        try {
            $timezone = SystemSetting::get(SystemSetting::TIMEZONE_KEY);
        } catch (Throwable) {
            return;
        }

        if (is_string($timezone) && $timezone !== config('app.timezone') && in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }
    }
}
