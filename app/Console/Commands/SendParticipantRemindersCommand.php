<?php

namespace App\Console\Commands;

use App\Services\ParticipantReminders;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Recordatorios diarios al participante (Avisos en la app): cuotas, fecha límite de pago,
 * documentos pendientes y cita de visa. Programado en routes/console.php.
 */
class SendParticipantRemindersCommand extends Command
{
    protected $signature = 'participants:send-reminders
        {--dry-run : Muestra lo que enviaría sin escribir nada}
        {--date= : Fecha de referencia YYYY-MM-DD (por defecto hoy en America/Asuncion)}';

    protected $description = 'Avisos al participante por cuotas, pagos, documentos pendientes y cita de visa';

    public function handle(ParticipantReminders $reminders): int
    {
        $tz = 'America/Asuncion';
        try {
            $today = $this->option('date') ? CarbonImmutable::createFromFormat('Y-m-d', $this->option('date'), $tz)->startOfDay() : CarbonImmutable::today($tz);
        } catch (\Throwable) {
            $this->error('Fecha inválida. Formato esperado: YYYY-MM-DD.');

            return self::FAILURE;
        }
        $dryRun = (bool) $this->option('dry-run');
        $this->info(($dryRun ? '[DRY-RUN] ' : '').'Recordatorios al participante al '.$today->format('d/m/Y'));

        $result = $reminders->run($today, $dryRun);
        if ($result['actions'] === []) {
            $this->line('Sin recordatorios para hoy.');
        } else {
            $this->table(['Evento', 'Usuario', 'Clave', 'Título'], array_map(fn ($a) => [$a['event'], $a['user'], $a['key'], $a['title']], $result['actions']));
            $this->table(['Evento', 'Cantidad'], array_map(fn ($k, $v) => [$k, $v], array_keys($result['counts']), $result['counts']));
        }

        return self::SUCCESS;
    }
}
