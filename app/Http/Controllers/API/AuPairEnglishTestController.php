<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Concerns\ResolvesAuPairProcess;
use App\Models\AuPairEnglishTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $used = $process->englishTests()->count();
        if ($used >= AuPairEnglishTest::maxAttempts()) {
            return response()->json(['status' => 'error', 'message' => 'Alcanzaste el máximo de intentos.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'exam_name' => 'required|string|max:80',
            'oral_score' => 'nullable|in:Good,Great,Excellent',
            'listening_score' => 'nullable|integer|min:0|max:100',
            'reading_score' => 'nullable|integer|min:0|max:100',
            'final_score' => 'required|integer|min:0|max:100',
            'observations' => 'nullable|string|max:600',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $pdfPath = null;
        if ($request->hasFile('pdf')) {
            $pdfPath = $request->file('pdf')->store("au_pair/{$process->id}/english_tests", 'public');
        }

        $finalScore = (int) $request->input('final_score');
        $test = AuPairEnglishTest::create([
            'au_pair_process_id' => $process->id,
            'exam_name' => $request->input('exam_name'),
            'oral_score' => $request->input('oral_score'),
            'listening_score' => $request->input('listening_score'),
            'reading_score' => $request->input('reading_score'),
            'final_score' => $finalScore,
            'cefr_level' => AuPairEnglishTest::scoreToLevel($finalScore),
            'observations' => $request->input('observations'),
            'test_pdf_path' => $pdfPath,
            'attempt_number' => $used + 1,
        ]);

        return response()->json(['status' => 'success', 'data' => $this->serialize($test)], 201);
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
