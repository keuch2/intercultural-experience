<?php

namespace App\Http\Controllers\API\Concerns;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use Illuminate\Http\Request;

/**
 * Helper compartido por todos los API controllers Au Pair:
 * resuelve el AuPairProcess del user autenticado (se crea al aprobar la postulación).
 */
trait ResolvesAuPairProcess
{
    /**
     * @param  bool  $allowPreview  true solo para la vista del proceso: con la postulación sin aprobar se
     *                              devuelve un proceso en memoria; el resto responde 403 hasta la aprobación.
     * @return array{0: ?AuPairProcess, 1: ?\Illuminate\Http\JsonResponse}
     */
    protected function resolveProcess(Request $request, bool $allowPreview = false): array
    {
        $user = $request->user();
        // Postulación seleccionada en la app (X-Application-Id) o la más reciente (sin las rechazadas).
        $base = Application::query()
            ->where('user_id', $user->id)
            ->visibleToParticipant()
            ->whereHas('program', fn ($q) => $q->where('subcategory', Program::SUBCATEGORY_AU_PAIR));
        $selectedId = (int) ($request->header('X-Application-Id') ?: $request->query('application_id'));
        $application = ($selectedId ? (clone $base)->whereKey($selectedId)->first() : null)
            ?? $base->latest('id')->first();

        if (! $application) {
            return [null, response()->json([
                'status' => 'no_application',
                'message' => 'No tenés una postulación Au Pair activa.',
            ], 404)];
        }

        // El proceso se crea al aprobar (ApplicationReviewService); una aprobada sin proceso (datos viejos) lo crea.
        $process = $application->isApproved()
            ? AuPairProcess::firstOrCreate(['application_id' => $application->id], AuPairProcess::initialAttributes($application))->setRelation('application', $application)
            : AuPairProcess::forApplicationOrPreview($application);

        if (! $process->exists && ! $allowPreview) {
            return [null, response()->json([
                'status' => 'application_pending',
                'message' => 'Tu postulación está pendiente de aprobación. Te avisaremos cuando IE la apruebe.',
            ], 403)];
        }

        return [$process, null];
    }

    /**
     * El postulante debe estar aprobado por el Staff IE (application.status === 'approved')
     * antes de poder ver/subir documentos. Hasta entonces el flujo queda bloqueado.
     */
    protected function applicantApproved(AuPairProcess $process): bool
    {
        return optional($process->application)->status === 'approved';
    }
}
