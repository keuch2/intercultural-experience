<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\Controller;
use App\Services\ProgramEngine\EnvelopeBuilder;
use App\Services\ProgramEngine\ProcessResolver;
use Illuminate\Http\Request;

/**
 * GET /api/me/process — última postulación del usuario en un programa del motor.
 * Punto de arranque de la app para decidir el flujo (aupair | engine | none).
 */
class MeController extends Controller
{
    public function process(Request $request, ProcessResolver $resolver, EnvelopeBuilder $envelope)
    {
        $process = $resolver->latestForUser($request->user());

        if (! $process) {
            return response()->json(['status' => 'no_application', 'data' => null], 404);
        }

        return response()->json(['status' => 'success', 'data' => $envelope->build($process)]);
    }
}
