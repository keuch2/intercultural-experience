<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Concerns\ResolvesAuPairProcess;
use Illuminate\Http\Request;

/**
 * GET /api/au-pair/support-logs
 *
 * Lista los logs de seguimiento visibles al participante.
 * Read-only en V1.
 */
class AuPairSupportLogController extends Controller
{
    use ResolvesAuPairProcess;

    public function index(Request $request)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $logs = $process->supportLogs()->get();
        return response()->json([
            'status' => 'success',
            'data' => $logs->map(fn ($log) => [
                'id' => $log->id,
                'log_type' => $log->log_type,
                'log_type_label' => $log->log_type_label,
                'title' => $log->title,
                'description' => $log->description,
                'log_date' => optional($log->log_date)->toDateString(),
                'follow_up_number' => $log->follow_up_number,
                'severity' => $log->severity,
                'severity_label' => $log->severity_label ?? null,
                'resolution' => $log->resolution,
                'resolved_at' => optional($log->resolved_at)->toIso8601String(),
            ])->values(),
        ]);
    }
}
