<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use Illuminate\Http\Request;

class JobOfferController extends Controller
{
    /**
     * @OA\Get(
     *     path="/job-offers",
     *     tags={"Job Offers"},
     *     summary="Listar ofertas laborales disponibles",
     *     description="Obtiene todas las ofertas laborales disponibles con filtros opcionales",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filtrar por ciudad",
     *         required=false,
     *         @OA\Schema(type="string", example="Miami")
     *     ),
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", example="Florida")
     *     ),
     *     @OA\Parameter(
     *         name="english_level",
     *         in="query",
     *         description="Filtrar por nivel de inglés requerido",
     *         required=false,
     *         @OA\Schema(type="string", enum={"A2","B1","B1+","B2","C1","C2"})
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="query",
     *         description="Filtrar por género requerido",
     *         required=false,
     *         @OA\Schema(type="string", enum={"male","female","any"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Resultados por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de ofertas obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="job_offer_id", type="string", example="JO-2025-001"),
     *                         @OA\Property(property="position", type="string", example="Lifeguard"),
     *                         @OA\Property(property="city", type="string", example="Miami"),
     *                         @OA\Property(property="state", type="string", example="Florida"),
     *                         @OA\Property(property="salary_min", type="number", example=2000),
     *                         @OA\Property(property="salary_max", type="number", example=2500),
     *                         @OA\Property(property="available_slots", type="integer", example=5)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     * 
     * Display available job offers with filters
     */
    public function index(Request $request)
    {
        $query = JobOffer::with(['sponsor', 'hostCompany'])
            ->available();

        // Filtros
        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        if ($request->has('state')) {
            $query->byState($request->state);
        }

        if ($request->has('english_level')) {
            $query->byEnglishLevel($request->english_level);
        }

        if ($request->has('gender')) {
            $query->byGender($request->gender);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $offers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/job-offers/{id}",
     *     tags={"Job Offers"},
     *     summary="Obtener detalles de una oferta laboral",
     *     description="Obtiene los detalles completos de una oferta laboral específica",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la oferta laboral",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la oferta obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="job_offer_id", type="string", example="JO-2025-001"),
     *                 @OA\Property(property="position", type="string", example="Lifeguard"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="city", type="string", example="Miami"),
     *                 @OA\Property(property="state", type="string", example="Florida"),
     *                 @OA\Property(property="salary_min", type="number", example=2000),
     *                 @OA\Property(property="salary_max", type="number", example=2500),
     *                 @OA\Property(property="hours_per_week", type="integer", example=40),
     *                 @OA\Property(property="housing_type", type="string", example="provided"),
     *                 @OA\Property(property="total_slots", type="integer", example=10),
     *                 @OA\Property(property="available_slots", type="integer", example=5),
     *                 @OA\Property(property="required_english_level", type="string", example="B2"),
     *                 @OA\Property(property="required_gender", type="string", example="any"),
     *                 @OA\Property(property="start_date", type="string", format="date"),
     *                 @OA\Property(property="end_date", type="string", format="date")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Oferta no encontrada")
     * )
     * 
     * Display the specified job offer
     */
    public function show($id)
    {
        $offer = JobOffer::with(['sponsor', 'hostCompany', 'reservations'])
            ->find($id);

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Oferta laboral no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $offer,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/job-offers/recommended",
     *     tags={"Job Offers"},
     *     summary="Obtener ofertas recomendadas",
     *     description="Obtiene ofertas laborales recomendadas basadas en el perfil del usuario (nivel de inglés y género)",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Ofertas recomendadas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="position", type="string"),
     *                     @OA\Property(property="city", type="string"),
     *                     @OA\Property(property="match_score", type="integer", example=95, description="Porcentaje de compatibilidad")
     *                 )
     *             )
     *         )
     *     )
     * )
     * 
     * Get recommended job offers for authenticated user
     */
    public function recommended(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 10);

        $recommendedOffers = JobOffer::getRecommendedForUser($user, $limit);

        return response()->json([
            'success' => true,
            'data' => $recommendedOffers,
            'message' => 'Ofertas personalizadas según tu perfil',
        ]);
    }

    /**
     * Search job offers
     */
    public function search(Request $request)
    {
        $searchTerm = $request->get('q');

        if (!$searchTerm) {
            return response()->json([
                'success' => false,
                'message' => 'Término de búsqueda requerido',
            ], 422);
        }

        $offers = JobOffer::with(['sponsor', 'hostCompany'])
            ->available()
            ->where(function($query) use ($searchTerm) {
                $query->where('position', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('city', 'like', "%{$searchTerm}%")
                    ->orWhere('state', 'like', "%{$searchTerm}%");
            })
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Get job offers by location
     */
    public function byLocation(Request $request)
    {
        $state = $request->get('state');
        $city = $request->get('city');

        $query = JobOffer::with(['sponsor', 'hostCompany'])
            ->available();

        if ($state) {
            $query->byState($state);
        }

        if ($city) {
            $query->byCity($city);
        }

        $offers = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Get available states with job offers
     */
    public function states()
    {
        $states = JobOffer::available()
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        return response()->json([
            'success' => true,
            'data' => $states,
        ]);
    }

    /**
     * Get available cities with job offers
     */
    public function cities(Request $request)
    {
        $query = JobOffer::available()
            ->select('city', 'state')
            ->distinct();

        if ($request->has('state')) {
            $query->where('state', $request->state);
        }

        $cities = $query->orderBy('city')->get();

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }
}
