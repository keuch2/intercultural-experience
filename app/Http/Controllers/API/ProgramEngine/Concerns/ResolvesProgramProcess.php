<?php

namespace App\Http\Controllers\API\ProgramEngine\Concerns;

use App\Models\Application;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\ProcessResolver;
use Illuminate\Http\Request;

/**
 * Resuelve el ProgramProcess del usuario autenticado en el programa
 * de la ruta. Espejo genérico de ResolvesAuPairProcess.
 */
trait ResolvesProgramProcess
{
    /**
     * @param  bool  $allowPreview  true solo para la vista del proceso: si la postulación todavía no fue
     *                              aprobada se devuelve un proceso en memoria (no se crea nada). El resto de
     *                              los endpoints responde 403 hasta que IE apruebe la solicitud.
     * @return array{0: ?ProgramProcess, 1: ?\Illuminate\Http\JsonResponse}
     */
    protected function resolveProcess(Request $request, Program $program, bool $allowPreview = false): array
    {
        // La app manda la postulación seleccionada (X-Application-Id) para ver una participación anterior;
        // sin ella (o si no corresponde a este usuario y programa) se usa la más reciente.
        // Las rechazadas quedan solo como historial interno.
        $base = Application::query()->where('user_id', $request->user()->id)->where('program_id', $program->id)->visibleToParticipant();
        $selectedId = (int) ($request->header('X-Application-Id') ?: $request->query('application_id'));
        $application = ($selectedId ? (clone $base)->whereKey($selectedId)->first() : null)
            ?? $base->latest('id')->first();

        if (! $application) {
            return [null, response()->json([
                'status' => 'no_application',
                'message' => "No tenés una postulación activa en {$program->name}.",
            ], 404)];
        }

        $process = app(ProcessResolver::class)->forApplicationOrPreview($application->setRelation('program', $program));

        if (! $process->exists && ! $allowPreview) {
            return [null, response()->json([
                'status' => 'application_pending',
                'message' => 'Tu postulación está pendiente de aprobación. Te avisaremos cuando IE la apruebe.',
            ], 403)];
        }

        return [$process, null];
    }
}
