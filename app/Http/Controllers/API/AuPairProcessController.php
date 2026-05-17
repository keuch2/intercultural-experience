<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuPairDocument;
use App\Models\AuPairProcess;
use App\Models\Program;
use Illuminate\Http\Request;

/**
 * GET /api/au-pair/process
 *
 * Devuelve el AuPairProcess del usuario autenticado. Si el user tiene una
 * Application Au Pair pero todavía no tiene proceso, lo crea automáticamente
 * (mismo patrón que AuPairProfileController::show()).
 *
 * Respuesta incluye:
 *  - stages: array de las 5 etapas con su estado computado.
 *  - statuses por stage, flags de pago y contrato.
 *  - progress_pct: 0..100 (basado en stage actual + documentos aprobados).
 *  - next_action: hint de la próxima acción que debe tomar el participante.
 */
class AuPairProcessController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $application = Application::query()
            ->where('user_id', $user->id)
            ->whereHas('program', fn ($q) => $q->where('subcategory', Program::SUBCATEGORY_AU_PAIR))
            ->latest('id')
            ->first();

        if (! $application) {
            return response()->json([
                'status' => 'no_application',
                'message' => 'El usuario no tiene una postulación Au Pair activa.',
            ], 404);
        }

        $process = AuPairProcess::firstOrCreate(
            ['application_id' => $application->id],
            [
                'user_id' => $user->id,
                'enrollment_date' => ($application->applied_at ?? $application->created_at)?->toDateString(),
                'enrollment_city' => $user->city,
                'enrollment_country' => $user->country,
                'current_stage' => 'admission',
                'admission_status' => 'pending',
                'application_status' => 'locked',
                'match_visa_status' => 'locked',
                'support_status' => 'locked',
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $this->transform($process),
        ]);
    }

    private function transform(AuPairProcess $p): array
    {
        return [
            'id' => $p->id,
            'application_id' => $p->application_id,
            'current_stage' => $p->current_stage,
            'enrollment_date' => optional($p->enrollment_date)->toDateString(),
            'statuses' => [
                'admission' => $p->admission_status,
                'application' => $p->application_status,
                'match_visa' => $p->match_visa_status,
                'support' => $p->support_status,
            ],
            'flags' => [
                'welcome_email_sent' => (bool) $p->welcome_email_sent,
                'interview_process_email_sent' => (bool) $p->interview_process_email_sent,
                'all_docs_and_payments_complete' => (bool) $p->all_docs_and_payments_complete,
                'itep_completed' => (bool) $p->itep_completed,
                'contract_signed' => (bool) $p->contract_signed,
                'payment_1_verified' => (bool) $p->payment_1_verified,
                'payment_2_verified' => (bool) $p->payment_2_verified,
            ],
            'stages' => $this->stagesSummary($p),
            'progress_pct' => $this->computeProgress($p),
            'next_action' => $this->computeNextAction($p),
            'finalization' => $p->finalization_result ? [
                'result' => $p->finalization_result,
                'reason' => $p->finalization_reason,
                'date' => optional($p->finalization_date)->toDateString(),
            ] : null,
        ];
    }

    private function stagesSummary(AuPairProcess $p): array
    {
        $current = $p->current_stage;
        $order = ['admission', 'application', 'match_visa', 'support', 'completed'];
        $currentIdx = array_search($current, $order, true);

        return collect(['admission', 'application', 'match_visa', 'support', 'completed'])
            ->map(function ($s) use ($p, $current, $currentIdx, $order) {
                $idx = array_search($s, $order, true);
                $state = 'locked';
                if ($idx < $currentIdx) {
                    $state = 'complete';
                } elseif ($s === $current) {
                    $state = 'in_progress';
                }
                return [
                    'key' => $s,
                    'label' => $this->stageLabel($s),
                    'state' => $state,
                ];
            })
            ->values()
            ->all();
    }

    private function stageLabel(string $s): string
    {
        return [
            'admission' => 'Admisión',
            'application' => 'Aplicación',
            'match_visa' => 'Match y Visa',
            'support' => 'Soporte',
            'completed' => 'Finalizado',
        ][$s] ?? $s;
    }

    /**
     * Progreso global aproximado: 25% por stage avanzado, +25% adicional cuando
     * los documentos del stage actual están todos aprobados.
     */
    private function computeProgress(AuPairProcess $p): int
    {
        $base = match ($p->current_stage) {
            'admission' => 0,
            'application' => 25,
            'match_visa' => 50,
            'support' => 75,
            'completed' => 100,
            default => 0,
        };

        if ($p->current_stage === 'completed') {
            return 100;
        }

        $stageDocsCfg = AuPairDocument::documentTypesForStage(
            $p->current_stage === 'application' ? 'application_payment1' : $p->current_stage
        );
        $required = collect($stageDocsCfg)->filter(fn ($d) => $d['required'])->keys();
        if ($required->isEmpty()) {
            return $base;
        }

        $docs = $p->documents()->whereIn('document_type', $required)->get();
        $approved = $required->filter(
            fn ($type) => $docs->where('document_type', $type)->where('status', 'approved')->isNotEmpty()
        )->count();

        $extra = (int) round(($approved / $required->count()) * 25);
        return min(100, $base + $extra);
    }

    private function computeNextAction(AuPairProcess $p): array
    {
        if ($p->current_stage === 'completed') {
            return ['key' => 'done', 'label' => '¡Felicitaciones! Has finalizado el programa.', 'screen' => null];
        }

        if ($p->current_stage === 'admission' && ! $p->admissionDocsApproved()) {
            return ['key' => 'upload_admission_docs', 'label' => 'Subí los documentos de admisión', 'screen' => 'AuPairDocuments', 'params' => ['stage' => 'admission']];
        }

        if ($p->current_stage === 'application') {
            if (! $p->payment_1_verified) {
                return ['key' => 'pay_1', 'label' => 'Registrá el pago de aplicación (Payment 1)', 'screen' => 'Payments'];
            }
            if (! $p->contract_signed) {
                return ['key' => 'sign_contract', 'label' => 'Esperá la confirmación del contrato firmado', 'screen' => null];
            }
            return ['key' => 'upload_application_docs', 'label' => 'Subí los documentos de aplicación', 'screen' => 'AuPairDocuments', 'params' => ['stage' => 'application_payment1']];
        }

        if ($p->current_stage === 'match_visa') {
            return ['key' => 'visa_in_progress', 'label' => 'Seguí tu proceso de visa y match', 'screen' => 'AuPairVisa'];
        }

        if ($p->current_stage === 'support') {
            return ['key' => 'support', 'label' => 'Disfrutá tu programa. Tu coordinador te acompañará.', 'screen' => 'AuPairSupport'];
        }

        return ['key' => 'wait', 'label' => 'Esperá la revisión del equipo IE', 'screen' => null];
    }
}
