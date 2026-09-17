<?php

namespace App\Console\Commands;

use App\Services\ProgramDatesAutomation;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Automatización diaria por fechas del programa (alertas a IE y paso a Support).
 *
 * Uso: php artisan programs:process-dates [--dry-run] [--date=YYYY-MM-DD]
 * Programado a diario en routes/console.php (07:00 America/Asuncion).
 */
class ProcessProgramDatesCommand extends Command
{
    protected $signature = 'programs:process-dates
        {--dry-run : Muestra lo que haría sin escribir nada}
        {--date= : Fecha de referencia YYYY-MM-DD (por defecto hoy en America/Asuncion)}';

    protected $description = 'Alertas a IE por inicio/fin del programa y paso automático a Support el día de inicio';

    public function handle(ProgramDatesAutomation $automation): int
    {
        $tz = 'America/Asuncion';
        try {
            $today = $this->option('date')
                ? CarbonImmutable::createFromFormat('Y-m-d', $this->option('date'), $tz)->startOfDay()
                : CarbonImmutable::today($tz);
        } catch (\Throwable) {
            $this->error('Fecha inválida. Formato esperado: YYYY-MM-DD.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info(($dryRun ? '[DRY-RUN] ' : '').'Procesando fechas de programa al '.$today->format('d/m/Y'));

        $result = $automation->run($today, $dryRun);

        if ($result['actions'] === []) {
            $this->line('Sin acciones para hoy.');
        } else {
            $this->table(['Módulo', 'Evento', 'Proceso', 'Participante', 'Detalle'], array_map(fn ($a) => [$a['scope'], $a['event'], $a['process'], $a['user'], $a['detail']], $result['actions']));
            $this->table(['Evento', 'Cantidad'], array_map(fn ($k, $v) => [$k, $v], array_keys($result['counts']), $result['counts']));
        }

        return self::SUCCESS;
    }
}
