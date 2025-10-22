<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnglishEvaluation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnglishEvaluationController extends Controller
{
    /**
     * Display a listing of evaluations.
     */
    public function index(Request $request)
    {
        $query = EnglishEvaluation::with('user')
            ->orderBy('evaluated_at', 'desc');

        // Filtro por nivel CEFR
        if ($request->filled('cefr_level')) {
            $query->where('cefr_level', $request->cefr_level);
        }

        // Filtro por clasificación
        if ($request->filled('classification')) {
            $query->where('classification', $request->classification);
        }

        // Filtro por participante
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $evaluations = $query->paginate(20);

        // Estadísticas generales
        $stats = [
            'total' => EnglishEvaluation::count(),
            'excellent' => EnglishEvaluation::where('classification', 'EXCELLENT')->count(),
            'great' => EnglishEvaluation::where('classification', 'GREAT')->count(),
            'good' => EnglishEvaluation::where('classification', 'GOOD')->count(),
            'insufficient' => EnglishEvaluation::where('classification', 'INSUFFICIENT')->count(),
            'average_score' => EnglishEvaluation::avg('score'),
        ];

        return view('admin.english-evaluations.index', compact('evaluations', 'stats'));
    }

    /**
     * Show the form for creating a new evaluation.
     */
    public function create()
    {
        // Participantes que aún no han alcanzado el límite de 3 intentos
        $participants = User::where('role', 'user')
            ->where(function($query) {
                $query->whereDoesntHave('englishEvaluations')
                    ->orWhereHas('englishEvaluations', function ($q) {
                        $q->select('user_id', \DB::raw('COUNT(*) as count'))
                            ->groupBy('user_id')
                            ->havingRaw('COUNT(*) < 3');
                    });
            })
            ->orderBy('name')
            ->get();

        return view('admin.english-evaluations.create', compact('participants'));
    }

    /**
     * Store a newly created evaluation in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'score' => 'required|integer|min:0|max:100',
            'listening_score' => 'nullable|integer|min:0|max:100',
            'reading_score' => 'nullable|integer|min:0|max:100',
            'writing_score' => 'nullable|integer|min:0|max:100',
            'speaking_score' => 'nullable|integer|min:0|max:100',
            'evaluated_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Verificar que el usuario no haya excedido los 3 intentos
        $attemptCount = EnglishEvaluation::where('user_id', $validated['user_id'])->count();
        
        if ($attemptCount >= 3) {
            return back()->with('error', 'El participante ya ha completado los 3 intentos permitidos.');
        }

        // Calcular número de intento
        $validated['attempt_number'] = $attemptCount + 1;
        
        // Fecha de evaluación
        $validated['evaluated_at'] = now();

        // Crear evaluación (el modelo calculará automáticamente CEFR y clasificación)
        $evaluation = EnglishEvaluation::create($validated);

        return redirect()
            ->route('admin.english-evaluations.show', $evaluation->id)
            ->with('success', 'Evaluación registrada exitosamente. Nivel: ' . $evaluation->cefr_level);
    }

    /**
     * Display the specified evaluation.
     */
    public function show($id)
    {
        $evaluation = EnglishEvaluation::with('user')->findOrFail($id);
        
        // Historial de evaluaciones del usuario
        $userEvaluations = EnglishEvaluation::where('user_id', $evaluation->user_id)
            ->orderBy('attempt_number')
            ->get();

        return view('admin.english-evaluations.show', compact('evaluation', 'userEvaluations'));
    }

    /**
     * Show the dashboard with statistics.
     */
    public function dashboard()
    {
        // KPIs generales
        $totalEvaluations = EnglishEvaluation::count();
        $uniqueParticipants = EnglishEvaluation::distinct('user_id')->count();
        $averageScore = round(EnglishEvaluation::avg('score'), 1);
        
        // Distribución por nivel CEFR
        $cefrDistribution = EnglishEvaluation::select('cefr_level', DB::raw('count(*) as count'))
            ->groupBy('cefr_level')
            ->orderBy('cefr_level')
            ->get();

        // Distribución por clasificación
        $classificationDistribution = EnglishEvaluation::select('classification', DB::raw('count(*) as count'))
            ->groupBy('classification')
            ->get();

        // Mejores evaluaciones recientes (últimos 30 días)
        $recentBest = EnglishEvaluation::with('user')
            ->where('evaluated_at', '>=', now()->subDays(30))
            ->orderBy('score', 'desc')
            ->limit(10)
            ->get();

        // Participantes que necesitan re-evaluación (score < 60)
        $needReevaluation = EnglishEvaluation::with('user')
            ->where('classification', 'INSUFFICIENT')
            ->whereHas('user', function ($query) {
                $query->whereHas('englishEvaluations', function ($q) {
                    $q->select('user_id')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) < 3');
                });
            })
            ->orderBy('evaluated_at', 'desc')
            ->limit(10)
            ->get();

        // Evolución mensual (últimos 6 meses)
        $monthlyEvolution = EnglishEvaluation::select(
                DB::raw('DATE_FORMAT(evaluated_at, "%Y-%m") as month'),
                DB::raw('AVG(score) as avg_score'),
                DB::raw('COUNT(*) as count')
            )
            ->where('evaluated_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.english-evaluations.dashboard', compact(
            'totalEvaluations',
            'uniqueParticipants',
            'averageScore',
            'cefrDistribution',
            'classificationDistribution',
            'recentBest',
            'needReevaluation',
            'monthlyEvolution'
        ));
    }

    /**
     * Remove the specified evaluation from storage.
     */
    public function destroy($id)
    {
        $evaluation = EnglishEvaluation::findOrFail($id);
        
        $userName = $evaluation->user->name;
        $evaluation->delete();

        return redirect()
            ->route('admin.english-evaluations.index')
            ->with('success', "Evaluación de {$userName} eliminada exitosamente.");
    }
}
