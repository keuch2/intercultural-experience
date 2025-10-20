<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EnglishEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnglishEvaluationController extends Controller
{
    /**
     * Display a listing of user's evaluations
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $evaluations = EnglishEvaluation::where('user_id', $user->id)
            ->orderBy('evaluated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $evaluations,
            'remaining_attempts' => EnglishEvaluation::remainingAttempts($user->id),
            'can_attempt' => EnglishEvaluation::canAttempt($user->id),
        ]);
    }

    /**
     * Store a new evaluation
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Verificar si puede hacer otro intento
        if (!EnglishEvaluation::canAttempt($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Has alcanzado el límite máximo de 3 intentos',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'score' => 'required|integer|min:0|max:100',
            'ef_set_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $score = $request->score;
        $attemptNumber = EnglishEvaluation::where('user_id', $user->id)->count() + 1;

        $evaluation = EnglishEvaluation::create([
            'user_id' => $user->id,
            'ef_set_id' => $request->ef_set_id,
            'score' => $score,
            'cefr_level' => EnglishEvaluation::getCefrLevel($score),
            'classification' => EnglishEvaluation::classifyScore($score),
            'attempt_number' => $attemptNumber,
            'evaluated_at' => now(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evaluación registrada exitosamente',
            'data' => $evaluation,
            'remaining_attempts' => EnglishEvaluation::remainingAttempts($user->id),
        ], 201);
    }

    /**
     * Display the specified evaluation
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $evaluation = EnglishEvaluation::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$evaluation) {
            return response()->json([
                'success' => false,
                'message' => 'Evaluación no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $evaluation,
        ]);
    }

    /**
     * Get best evaluation
     */
    public function best(Request $request)
    {
        $user = $request->user();
        
        $bestEvaluation = EnglishEvaluation::where('user_id', $user->id)
            ->orderBy('score', 'desc')
            ->first();

        if (!$bestEvaluation) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes evaluaciones registradas',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bestEvaluation,
        ]);
    }

    /**
     * Get evaluation statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $evaluations = EnglishEvaluation::where('user_id', $user->id)->get();

        if ($evaluations->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_attempts' => 0,
                    'remaining_attempts' => 3,
                    'best_score' => null,
                    'average_score' => null,
                    'current_level' => null,
                ],
            ]);
        }

        $bestEvaluation = $evaluations->sortByDesc('score')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_attempts' => $evaluations->count(),
                'remaining_attempts' => EnglishEvaluation::remainingAttempts($user->id),
                'best_score' => $bestEvaluation->score,
                'average_score' => round($evaluations->avg('score'), 2),
                'current_level' => $bestEvaluation->cefr_level,
                'classification' => $bestEvaluation->classification,
                'evaluations' => $evaluations,
            ],
        ]);
    }
}
