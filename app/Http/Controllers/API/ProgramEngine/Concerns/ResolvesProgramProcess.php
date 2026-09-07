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
        $application = Application::query()
            ->where('user_id', $request->user()->id)
            ->where('program_id', $program->id)
            ->latest('id')
            ->first();

        if (! $application) {
            return [null, response()->json([
                'status' => 'no_application',
                'message' => "No tenés una postulación activa en {$program->name}.",
            ], 404)];
        }

        return [app(ProcessResolver::class)->forApplication($application), null];
    }
}
