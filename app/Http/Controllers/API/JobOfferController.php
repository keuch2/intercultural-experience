<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use Illuminate\Http\Request;

class JobOfferController extends Controller
{
    /**
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
