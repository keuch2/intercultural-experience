<?php

namespace App\Services;

use App\Models\AuPairProcess;
use App\Models\AuPairSupportLog;
use App\Models\ProgramProcess;
use App\Models\ProgramSupportLog;
use App\Models\User;
use App\Services\ProgramEngine\Notifier;

/**
 * Reportes de soporte creados por el participante desde la app (motor y Au Pair):
 * se guardan como registros de Support con source=participant y avisan a IE.
 */
class ParticipantSupportReports
{
    public const TYPES = ['participant_report', 'incident'];

    public const LABELS = ['participant_report' => 'Reporte / consulta', 'incident' => 'Incidente'];

    public const CATEGORY = 'support';

    public function __construct(private readonly Notifier $notifier) {}

    /**
     * @param  array{log_type:string,title:string,description:string,urgent?:bool}  $data
     */
    public function create(ProgramProcess|AuPairProcess $process, User $user, array $data, string $adminUrl, string $programName): ProgramSupportLog|AuPairSupportLog
    {
        $urgent = (bool) ($data['urgent'] ?? false);
        $log = $process->supportLogs()->create([
            'log_type' => $data['log_type'],
            'title' => $data['title'],
            'description' => $data['description'],
            'log_date' => today(),
            'severity' => $urgent ? 'high' : null,
            'logged_by' => $user->id,
            'source' => 'participant',
        ]);

        $label = self::LABELS[$data['log_type']] ?? $data['log_type'];
        $this->notifier->toAdmins(
            'Nuevo reporte del participante: '.$user->name,
            "{$user->name} ({$programName}) envió un {$label}".($urgent ? ' marcado como URGENTE' : '').": \"{$data['title']}\". Revisalo en el tab Support: {$adminUrl}",
            self::CATEGORY
        );

        return $log;
    }

    public static function rules(): array
    {
        return [
            'log_type' => ['required', 'in:'.implode(',', self::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'urgent' => ['nullable', 'boolean'],
        ];
    }
}
