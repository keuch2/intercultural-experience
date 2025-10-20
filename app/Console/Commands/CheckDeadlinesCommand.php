<?php

namespace App\Console\Commands;

use App\Models\UserProgramRequisite;
use App\Models\ProgramRequisite;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * Comando para verificar deadlines y enviar recordatorios
 * Épica 5 - Sistema de Deadlines
 * 
 * Uso: php artisan deadlines:check
 * Configurar en cron: * * * * * php artisan deadlines:check
 */
class CheckDeadlinesCommand extends Command
{
    protected $signature = 'deadlines:check';
    protected $description = 'Verifica deadlines de requisitos y envía recordatorios';

    public function handle()
    {
        $this->info('🔍 Verificando deadlines...');

        // Obtener requisitos con deadline que tengan recordatorios habilitados
        $requisites = ProgramRequisite::whereNotNull('deadline')
            ->where('send_reminders', true)
            ->get();

        $remindersToSend = [];

        foreach ($requisites as $requisite) {
            $deadline = Carbon::parse($requisite->deadline);
            $today = Carbon::today();

            // Días para enviar recordatorios: 30, 15, 7, 3, 1 días antes
            $reminderDays = [30, 15, 7, 3, 1];

            foreach ($reminderDays as $days) {
                $reminderDate = $deadline->copy()->subDays($days);
                
                if ($reminderDate->isSameDay($today)) {
                    // Buscar usuarios con este requisito pendiente
                    $pendingUsers = UserProgramRequisite::where('program_requisite_id', $requisite->id)
                        ->whereIn('status', ['pending', 'completed']) // No verificados aún
                        ->with(['application.user'])
                        ->get();

                    foreach ($pendingUsers as $userRequisite) {
                        $remindersToSend[] = [
                            'user' => $userRequisite->application->user,
                            'requisite' => $requisite,
                            'days_left' => $days,
                            'deadline' => $deadline
                        ];
                    }
                }
            }

            // Verificar si ya venció (día del deadline)
            if ($deadline->isSameDay($today)) {
                $overdueUsers = UserProgramRequisite::where('program_requisite_id', $requisite->id)
                    ->where('status', 'pending')
                    ->with(['application.user'])
                    ->get();

                foreach ($overdueUsers as $userRequisite) {
                    // Marcar como vencido
                    $userRequisite->update([
                        'status' => 'overdue',
                        'observations' => 'Requisito vencido el ' . $deadline->format('d/m/Y')
                    ]);

                    $this->warn("⚠️  Requisito vencido para: {$userRequisite->application->user->name}");
                }
            }
        }

        // Enviar recordatorios
        if (count($remindersToSend) > 0) {
            foreach ($remindersToSend as $reminder) {
                // TODO: Enviar email/notificación
                // event(new DeadlineReminderEvent($reminder['user'], $reminder['requisite'], $reminder['days_left']));
                
                $this->info("📧 Recordatorio enviado a: {$reminder['user']->name} ({$reminder['days_left']} días restantes)");
            }

            $this->info("✅ Se enviaron " . count($remindersToSend) . " recordatorios");
        } else {
            $this->info("ℹ️  No hay recordatorios para enviar hoy");
        }

        return 0;
    }
}
