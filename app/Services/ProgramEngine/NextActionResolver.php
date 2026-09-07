<?php

namespace App\Services\ProgramEngine;

use App\Models\ProgramDocument;
use App\Models\ProgramProcess;

/**
 * Próxima acción sugerida al participante: {key, label, screen, params}.
 * El backend decide la pantalla (la app navega "a ciegas", como en Au Pair).
 */
class NextActionResolver
{
    public const SCREEN_DOCUMENTS = 'ProgramDocuments';

    public const SCREEN_PAYMENTS = 'Payments';

    public function __construct(private readonly StageEvaluator $evaluator = new StageEvaluator) {}

    public function resolve(ProgramProcess $process): array
    {
        $definition = ProgramDefinition::for($process->program);

        if ($process->status === ProgramProcess::STATUS_COMPLETED) {
            return $this->action('done', '¡Felicitaciones! Has finalizado el programa.');
        }
        if ($process->status === ProgramProcess::STATUS_CANCELLED) {
            return $this->action('cancelled', 'Tu proceso fue cancelado. Contactá al equipo IE.');
        }
        if (! $process->applicantApproved()) {
            return $this->action('wait_approval', 'Tu postulación está en revisión por el equipo IE.');
        }

        $stage = $definition->stage($process->current_stage_key);
        if (! $stage || $stage->is_terminal) {
            return $this->action('done', '¡Felicitaciones! Has finalizado el programa.');
        }

        $docs = $process->documents()->where('stage_key', $stage->key)->get();
        $hasPending = false;

        foreach ($definition->groups($stage->key) as $group) {
            $gate = $definition->gate($group['unlock_gate_key']);
            if ($gate && ! $process->isGateVerified($gate->key)) {
                return $this->action('pay_gate', "Registrá el pago: {$gate->label}", self::SCREEN_PAYMENTS, ['gate' => $gate->key]);
            }

            foreach ($definition->requirements($stage->key, $group['key']) as $req) {
                if ($req->uploaded_by !== 'participant' || ! $req->is_required) {
                    continue;
                }
                $forReq = $docs->where('requirement_key', $req->key);
                $approved = $forReq->where('status', ProgramDocument::STATUS_APPROVED)->count();
                if ($approved >= max(1, (int) $req->min_count)) {
                    continue;
                }
                if ($forReq->where('status', ProgramDocument::STATUS_PENDING)->isNotEmpty()) {
                    $hasPending = true;

                    continue;
                }

                return $this->action('upload_docs', "Subí los documentos de {$group['label']}", $stage->mobile_screen ?: self::SCREEN_DOCUMENTS, ['group' => $group['key']]);
            }
        }

        if ($hasPending) {
            return $this->action('wait_review', 'Tus documentos están en revisión por el equipo IE.', self::SCREEN_DOCUMENTS);
        }

        if ($stage->guardValue('require_job_assignment', false) && $definition->hasModule(ModuleCatalog::JOB_POOL)) {
            if (! $process->hasModuleAccess(ModuleCatalog::JOB_POOL)) {
                return $this->action('wait_enable', 'Esperá que el equipo IE habilite tu acceso al Pool de Ofertas.');
            }
            if (! $this->evaluator->hasActiveJobAssignment($process)) {
                return $this->action('select_offer', 'Elegí tu oferta laboral en el Pool de Ofertas', ModuleCatalog::get(ModuleCatalog::JOB_POOL)['mobile_screen']);
            }
        }

        if ($stage->mobile_screen) {
            return $this->action('stage_screen', "Seguí tu proceso: {$stage->label}", $stage->mobile_screen);
        }

        return $this->action('wait', 'Esperá la revisión del equipo IE.');
    }

    private function action(string $key, string $label, ?string $screen = null, array $params = []): array
    {
        return ['key' => $key, 'label' => $label, 'screen' => $screen, 'params' => $params ?: null];
    }
}
