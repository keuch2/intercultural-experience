<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\API\ProgramEngine\Concerns\ResolvesProgramProcess;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Services\ProgramEngine\EnvelopeBuilder;
use Illuminate\Http\Request;

/** GET /api/programs/{engineProgram}/process */
class ProcessController extends Controller
{
    use ResolvesProgramProcess;

    public function show(Request $request, Program $engineProgram, EnvelopeBuilder $envelope)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }

        return response()->json(['status' => 'success', 'data' => $envelope->build($process)]);
    }
}
