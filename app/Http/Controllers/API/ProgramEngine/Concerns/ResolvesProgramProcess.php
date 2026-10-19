<?php

namespace App\Http\Controllers\API\ProgramEngine\Concerns;

use App\Models\Application;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\ProcessResolver;
use Illuminate\Http\Request;

/**
 * Resuelve (o crea) el ProgramProcess del usuario autenticado en el programa
 * de la ruta. Espejo genérico de ResolvesAuPairProcess.
 */
trait ResolvesProgramProcess
{
    /** @return array{0: ?ProgramProcess, 1: ?\Illuminate\Http\JsonResponse} */
    protected function resolveProcess(Request $request, Program $program): array
    {
        // La app manda la postulación seleccionada (X-Application-Id) para ver una participación anterior;
        // sin ella (o si no corresponde a este usuario y programa) se usa la más reciente.
        $base = Application::query()->where('user_id', $request->user()->id)->where('program_id', $program->id);
        $selectedId = (int) ($request->header('X-Application-Id') ?: $request->query('application_id'));
        $application = ($selectedId ? (clone $base)->whereKey($selectedId)->first() : null)
            ?? $base->latest('id')->first();

        if (! $application) {
            return [null, response()->json([
                'status' => 'no_application',
                'message' => "No tenés una postulación activa en {$program->name}.",
            ], 404)];
        }

        return [app(ProcessResolver::class)->forApplication($application), null];
    }
}
