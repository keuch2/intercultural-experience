<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\API\ProgramEngine\Concerns\ResolvesProgramProcess;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Services\ProgramEngine\PlacementService;
use Illuminate\Http\Request;

/** GET /api/programs/{p}/placement — Job Placement (solo lectura para el participante). */
class PlacementController extends Controller
{
    use ResolvesProgramProcess;

    public function show(Request $request, Program $engineProgram, PlacementService $placements)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        $process->loadMissing(['placement.sponsor', 'program']);

        return response()->json(['status' => 'success', 'data' => $placements->toArray($process)]);
    }
}
