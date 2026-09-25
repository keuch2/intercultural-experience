<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Alertas a IE por inicio/fin del programa y paso automático a Support (requiere el cron de schedule:run).
Schedule::command('programs:process-dates')
    ->dailyAt('07:00')
    ->timezone('America/Asuncion')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/program-dates.log'));

// Recordatorios al participante (cuotas, pagos, documentos, cita de visa) → Avisos en la app.
Schedule::command('participants:send-reminders')
    ->dailyAt('08:00')
    ->timezone('America/Asuncion')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/participant-reminders.log'));
