<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\API\ProgramEngine\Concerns\ResolvesProgramProcess;
use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportLogController extends Controller
{
    use ResolvesProgramProcess;

    private const LABELS = [
        'arrival_followup' => 'Seguimiento de llegada', 'monthly_followup' => 'Seguimiento mensual', 'program_followup' => 'Seguimiento durante el programa',
        'incident' => 'Incidente', 'employer_change' => 'Cambio de empleador', 'experience_evaluation' => 'Evaluación de experiencia', 'final_evaluation' => 'Evaluación final',
    ];

    private const SEVERITY = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'critical' => 'Crítica'];

    public function index(Request $request, Program $engineProgram)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }

        return response()->json(['status' => 'success', 'data' => $process->supportLogs()->get()->map(fn ($log) => [
            'id' => $log->id,
            'log_type' => $log->log_type,
            'log_type_label' => self::LABELS[$log->log_type] ?? Str::headline($log->log_type),
            'title' => $log->title, 'description' => $log->description,
            'log_date' => optional($log->log_date)->toDateString(),
            'follow_up_number' => $log->follow_up_number,
            'severity' => $log->severity, 'severity_label' => self::SEVERITY[$log->severity] ?? null,
            'resolution' => $log->resolution, 'resolved_at' => optional($log->resolved_at)->toIso8601String(),
        ])->values()]);
    }
}
