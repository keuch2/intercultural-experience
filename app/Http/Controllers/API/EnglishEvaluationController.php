<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EnglishEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnglishEvaluationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/english-evaluations",
     *     tags={"English Tests"},
     *     summary="Listar evaluaciones de inglés del usuario",
     *     description="Obtiene todas las evaluaciones de inglés del usuario autenticado, ordenadas por fecha descendente",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de evaluaciones obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="score", type="integer", example=75),
     *                     @OA\Property(property="cefr_level", type="string", example="B2"),
     *                     @OA\Property(property="ef_set_id", type="string", example="EF123456"),
     *                     @OA\Property(property="evaluated_at", type="string", format="date-time", example="2025-10-20T14:30:00Z"),
     *                     @OA\Property(property="notes", type="string", example="Excelente comprensión oral")
     *                 )
     *             ),
     *             @OA\Property(property="remaining_attempts", type="integer", example=2),
     *             @OA\Property(property="can_attempt", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     * 
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
     * @OA\Post(
     *     path="/english-evaluations",
     *     tags={"English Tests"},
     *     summary="Registrar nueva evaluación de inglés",
     *     description="Registra una nueva evaluación de inglés. Máximo 3 intentos por usuario. El nivel CEFR se calcula automáticamente según el puntaje.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"score"},
     *             @OA\Property(property="score", type="integer", minimum=0, maximum=100, example=75, description="Puntaje obtenido (0-100)"),
     *             @OA\Property(property="ef_set_id", type="string", example="EF123456", description="ID del test EF SET (opcional)"),
     *             @OA\Property(property="notes", type="string", example="Test realizado en línea", description="Notas adicionales (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Evaluación registrada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Evaluación registrada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="score", type="integer", example=75),
     *                 @OA\Property(property="cefr_level", type="string", example="B2"),
     *                 @OA\Property(property="classification", type="string", example="Upper Intermediate"),
     *                 @OA\Property(property="attempt_number", type="integer", example=1)
     *             ),
     *             @OA\Property(property="remaining_attempts", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Límite de intentos alcanzado"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     * 
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
