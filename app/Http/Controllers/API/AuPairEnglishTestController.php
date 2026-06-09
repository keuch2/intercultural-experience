<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Concerns\ResolvesAuPairProcess;
use App\Models\AuPairEnglishTest;
use Illuminate\Http\Request;

/**
 * GET  /api/au-pair/english-tests
 * POST /api/au-pair/english-tests  (multipart con PDF opcional)
 *
 * Máximo 3 intentos por participante (AuPairEnglishTest::maxAttempts()).
 */
class AuPairEnglishTestController extends Controller
{
    use ResolvesAuPairProcess;

    public function index(Request $request)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $tests = $process->englishTests()->orderByDesc('attempt_number')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'max_attempts' => AuPairEnglishTest::maxAttempts(),
                'used_attempts' => $tests->count(),
                'remaining_attempts' => max(0, AuPairEnglishTest::maxAttempts() - $tests->count()),
                'tests' => $tests->map(fn ($t) => $this->serialize($t))->values(),
            ],
        ]);
    }

    /**
     * El test de inglés se rinde físicamente en oficinas de IE; los resultados los
     * carga el Staff IE desde el admin. El participante NO puede registrar/editar
     * su nivel desde la app — solo visualizarlo (GET index).
     */
    public function store(Request $request)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Los resultados de inglés los registra el equipo de IE en la oficina.',
        ], 403);
    }

    private function serialize(AuPairEnglishTest $t): array
    {
        return [
            'id' => $t->id,
            'exam_name' => $t->exam_name,
            'attempt_number' => $t->attempt_number,
            'oral_score' => $t->oral_score,
            'listening_score' => $t->listening_score,
            'reading_score' => $t->reading_score,
            'final_score' => $t->final_score,
            'cefr_level' => $t->cefr_level,
            'meets_minimum' => $t->meetsMinimumLevel(),
            'observations' => $t->observations,
            'pdf_url' => $t->test_pdf_path ? asset('storage/' . $t->test_pdf_path) : null,
            'created_at' => optional($t->created_at)->toIso8601String(),
        ];
    }
}
