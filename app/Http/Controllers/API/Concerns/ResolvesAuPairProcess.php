<?php

namespace App\Http\Controllers\API\Concerns;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use Illuminate\Http\Request;

/**
 * Helper compartido por todos los API controllers Au Pair:
 * resuelve (o crea) el AuPairProcess del user autenticado.
 */
trait ResolvesAuPairProcess
{
    /**
     * @return array{0: ?AuPairProcess, 1: ?\Illuminate\Http\JsonResponse}
     */
    protected function resolveProcess(Request $request): array
    {
        $user = $request->user();
        $application = Application::query()
            ->where('user_id', $user->id)
            ->whereHas('program', fn ($q) => $q->where('subcategory', Program::SUBCATEGORY_AU_PAIR))
            ->latest('id')
            ->first();

        if (! $application) {
            return [null, response()->json([
                'status' => 'no_application',
                'message' => 'No tenés una postulación Au Pair activa.',
            ], 404)];
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
