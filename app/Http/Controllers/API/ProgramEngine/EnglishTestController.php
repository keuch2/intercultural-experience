<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\API\ProgramEngine\Concerns\ResolvesProgramProcess;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnglishTest;
use App\Services\ProgramEngine\EnglishTestService;
use App\Services\ProgramEngine\ProgramDefinition;
use Illuminate\Http\Request;

class EnglishTestController extends Controller
{
    use ResolvesProgramProcess;

    public function index(Request $request, Program $engineProgram, EnglishTestService $english)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        $definition = ProgramDefinition::for($engineProgram);
        $min = $definition->minEnglishLevel();
        $tests = $process->englishTests()->orderByDesc('attempt_number')->get();

        return response()->json(['status' => 'success', 'data' => [
            'max_attempts' => $definition->maxEnglishAttempts(),
            'used_attempts' => $tests->count(),
            'remaining_attempts' => $english->remainingAttempts($process),
            'min_level' => $min,
            'best_level' => $english->bestLevel($process),
            'meets_minimum' => $english->meetsMinimum($process),
            'tests' => $tests->map(fn (ProgramEnglishTest $t) => [
                'id' => $t->id, 'exam_name' => $t->exam_name, 'attempt_number' => $t->attempt_number,
                'oral_score' => $t->oral_score, 'listening_score' => $t->listening_score, 'reading_score' => $t->reading_score,
                'final_score' => $t->final_score, 'cefr_level' => $t->cefr_level,
                'meets_minimum' => ProgramEnglishTest::levelMeets($t->cefr_level, $min),
                'observations' => $t->observations,
                'pdf_url' => $t->test_pdf_path ? asset('storage/'.$t->test_pdf_path) : null,
                'created_at' => optional($t->created_at)->toIso8601String(),
            ])->values(),
        ]]);
    }

    /** El test se rinde en oficinas de IE; los resultados los carga el staff. */
    public function store()
    {
        return response()->json(['status' => 'error', 'message' => 'Los resultados de inglés los registra el equipo de IE.'], 403);
    }
}
