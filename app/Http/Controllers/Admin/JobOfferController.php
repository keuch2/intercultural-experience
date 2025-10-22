<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use App\Models\Sponsor;
use App\Models\HostCompany;
use App\Models\User;
use App\Models\JobOfferReservation;
use App\Models\EnglishEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JobOfferController extends Controller
{
    /**
     * Display a listing of job offers
     */
    public function index(Request $request)
    {
        $query = JobOffer::with(['sponsor', 'hostCompany']);

        // Filtros
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('position', 'like', "%{$search}%")
                  ->orWhere('job_offer_id', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('sponsor_id')) {
            $query->where('sponsor_id', $request->sponsor_id);
        }

        if ($request->has('host_company_id')) {
            $query->where('host_company_id', $request->host_company_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('state')) {
            $query->byState($request->state);
        }

        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        $offers = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Para los filtros
        $sponsors = Sponsor::where('is_active', true)->get();
        $companies = HostCompany::where('is_active', true)->get();

        return view('admin.job-offers.index', compact('offers', 'sponsors', 'companies'));
    }

    /**
     * Show the form for creating a new job offer
     */
    public function create()
    {
        $sponsors = Sponsor::where('is_active', true)->get();
        $companies = HostCompany::where('is_active', true)->get();
        
        return view('admin.job-offers.create', compact('sponsors', 'companies'));
    }

    /**
     * Store a newly created job offer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_offer_id' => 'required|string|max:50|unique:job_offers,job_offer_id',
            'sponsor_id' => 'required|exists:sponsors,id',
            'host_company_id' => 'required|exists:host_companies,id',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|min:0|gte:salary_min',
            'hours_per_week' => 'required|integer|min:1|max:60',
            'housing_type' => 'required|in:provided,assisted,not_provided',
            'housing_cost' => 'nullable|numeric|min:0',
            'total_slots' => 'required|integer|min:1',
            'available_slots' => 'required|integer|min:0|lte:total_slots',
            'required_english_level' => 'required|in:A2,B1,B1+,B2,C1,C2',
            'required_gender' => 'required|in:male,female,any',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:available,full,cancelled',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $offer = JobOffer::create($request->all());

        return redirect()->route('admin.job-offers.index')
            ->with('success', 'Oferta laboral creada exitosamente');
    }

    /**
     * Display the specified job offer
     */
    public function show($id)
    {
        $offer = JobOffer::with(['sponsor', 'hostCompany', 'reservations.user'])
            ->findOrFail($id);

        return view('admin.job-offers.show', compact('offer'));
    }

    /**
     * Show the form for editing the specified job offer
     */
    public function edit($id)
    {
        $offer = JobOffer::findOrFail($id);
        $sponsors = Sponsor::where('is_active', true)->get();
        $companies = HostCompany::where('is_active', true)->get();
        
        return view('admin.job-offers.edit', compact('offer', 'sponsors', 'companies'));
    }

    /**
     * Update the specified job offer
     */
    public function update(Request $request, $id)
    {
        $offer = JobOffer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'job_offer_id' => 'required|string|max:50|unique:job_offers,job_offer_id,' . $id,
            'sponsor_id' => 'required|exists:sponsors,id',
            'host_company_id' => 'required|exists:host_companies,id',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|min:0|gte:salary_min',
            'hours_per_week' => 'required|integer|min:1|max:60',
            'housing_type' => 'required|in:provided,assisted,not_provided',
            'housing_cost' => 'nullable|numeric|min:0',
            'total_slots' => 'required|integer|min:1',
            'available_slots' => 'required|integer|min:0|lte:total_slots',
            'required_english_level' => 'required|in:A2,B1,B1+,B2,C1,C2',
            'required_gender' => 'required|in:male,female,any',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:available,full,cancelled',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $offer->update($request->all());

        return redirect()->route('admin.job-offers.index')
            ->with('success', 'Oferta laboral actualizada exitosamente');
    }

    /**
     * Remove the specified job offer
     */
    public function destroy($id)
    {
        $offer = JobOffer::findOrFail($id);

        // Verificar si tiene reservas activas
        if ($offer->reservations()->whereIn('status', ['pending', 'confirmed'])->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar la oferta porque tiene reservas activas');
        }

        $offer->delete();

        return redirect()->route('admin.job-offers.index')
            ->with('success', 'Oferta laboral eliminada exitosamente');
    }

    /**
     * Toggle job offer status
     */
    public function toggleStatus($id)
    {
        $offer = JobOffer::findOrFail($id);
        
        if ($offer->status === 'available') {
            $offer->update(['status' => 'cancelled']);
            $message = 'Oferta laboral cancelada';
        } else {
            $offer->update(['status' => 'available']);
            $message = 'Oferta laboral activada';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Dashboard with occupancy statistics
     */
    public function dashboard()
    {
        $totalOffers = JobOffer::count();
        $activeOffers = JobOffer::where('status', 'available')->count();
        $fullOffers = JobOffer::where('status', 'full')->count();
        
        // Ocupación general
        $totalSlots = JobOffer::sum('total_slots');
        $occupiedSlots = JobOffer::sum(DB::raw('total_slots - available_slots'));
        $occupancyRate = $totalSlots > 0 ? round(($occupiedSlots / $totalSlots) * 100, 1) : 0;

        // Top ofertas por ocupación
        $topOffers = JobOffer::with(['sponsor', 'hostCompany'])
            ->where('status', 'available')
            ->orderByRaw('(total_slots - available_slots) DESC')
            ->limit(10)
            ->get();

        // Ofertas por estado
        $offersByState = JobOffer::select('state', DB::raw('count(*) as count'))
            ->where('status', 'available')
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->get();

        // Reservas recientes
        $recentReservations = JobOfferReservation::with(['user', 'jobOffer'])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return view('admin.job-offers.dashboard', compact(
            'totalOffers',
            'activeOffers',
            'fullOffers',
            'totalSlots',
            'occupiedSlots',
            'occupancyRate',
            'topOffers',
            'offersByState',
            'recentReservations'
        ));
    }

    /**
     * Show matching candidates for a job offer
     */
    public function showMatching($id)
    {
        $offer = JobOffer::with(['sponsor', 'hostCompany'])->findOrFail($id);
        
        // Obtener candidatos que cumplen los requisitos
        $candidates = $this->findMatchingCandidates($offer);

        return view('admin.job-offers.matching', compact('offer', 'candidates'));
    }

    /**
     * Find matching candidates for a job offer
     */
    private function findMatchingCandidates($offer)
    {
        $query = User::role('participant')
            ->with(['englishEvaluations' => function($q) {
                $q->orderBy('score', 'desc')->limit(1);
            }]);

        // Filtrar por género si es requerido
        if ($offer->required_gender !== 'any') {
            $query->where('gender', $offer->required_gender);
        }

        $participants = $query->get();

        // Filtrar por nivel de inglés
        $requiredLevel = $offer->required_english_level;
        $levelHierarchy = ['A2' => 1, 'B1' => 2, 'B1+' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
        $requiredLevelValue = $levelHierarchy[$requiredLevel] ?? 0;

        $matchingCandidates = $participants->filter(function($participant) use ($requiredLevelValue, $levelHierarchy) {
            $lastEvaluation = $participant->englishEvaluations->first();
            
            if (!$lastEvaluation) {
                return false;
            }

            $participantLevelValue = $levelHierarchy[$lastEvaluation->cefr_level] ?? 0;
            
            return $participantLevelValue >= $requiredLevelValue;
        });

        // Verificar penalidades (3 rechazos = bloqueado)
        $matchingCandidates = $matchingCandidates->filter(function($participant) {
            $rejections = JobOfferReservation::where('user_id', $participant->id)
                ->where('status', 'rejected')
                ->count();
            
            return $rejections < 3;
        });

        // Verificar que no tengan ya una reserva activa en esta oferta
        $matchingCandidates = $matchingCandidates->filter(function($participant) use ($offer) {
            $hasActiveReservation = JobOfferReservation::where('user_id', $participant->id)
                ->where('job_offer_id', $offer->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();
            
            return !$hasActiveReservation;
        });

        // Calcular match score
        $matchingCandidates = $matchingCandidates->map(function($participant) use ($offer, $levelHierarchy, $requiredLevelValue) {
            $lastEvaluation = $participant->englishEvaluations->first();
            $participantLevelValue = $levelHierarchy[$lastEvaluation->cefr_level] ?? 0;
            
            // Score base por nivel de inglés
            $score = ($participantLevelValue >= $requiredLevelValue) ? 100 : 0;
            
            // Bonus por nivel superior al requerido
            if ($participantLevelValue > $requiredLevelValue) {
                $score += ($participantLevelValue - $requiredLevelValue) * 10;
            }
            
            // Penalidad por rechazos previos
            $rejections = JobOfferReservation::where('user_id', $participant->id)
                ->where('status', 'rejected')
                ->count();
            $score -= ($rejections * 15);
            
            $participant->match_score = max(0, $score);
            $participant->rejection_count = $rejections;
            
            return $participant;
        });

        // Ordenar por match score
        return $matchingCandidates->sortByDesc('match_score')->values();
    }

    /**
     * Assign a participant to a job offer
     */
    public function assignParticipant(Request $request, $offerId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $offer = JobOffer::findOrFail($offerId);
        $user = User::findOrFail($request->user_id);

        // Verificar que la oferta tiene cupos disponibles
        if ($offer->available_slots <= 0) {
            return back()->with('error', 'No hay cupos disponibles en esta oferta.');
        }

        // Verificar que el usuario no esté bloqueado por rechazos
        $rejections = JobOfferReservation::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->count();
        
        if ($rejections >= 3) {
            return back()->with('error', 'El participante está bloqueado por 3 rechazos previos.');
        }

        // Verificar que no tenga una reserva activa
        $existingReservation = JobOfferReservation::where('user_id', $user->id)
            ->where('job_offer_id', $offer->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingReservation) {
            return back()->with('error', 'El participante ya tiene una reserva activa en esta oferta.');
        }

        // Crear reserva
        DB::transaction(function() use ($offer, $user, $request) {
            // Crear la reserva
            JobOfferReservation::create([
                'job_offer_id' => $offer->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'reserved_at' => now(),
                'notes' => $request->notes,
            ]);

            // Reducir cupo disponible
            $offer->decrement('available_slots');

            // Actualizar estado de la oferta si se llenó
            if ($offer->available_slots <= 0) {
                $offer->update(['status' => 'full']);
            }
        });

        return back()->with('success', 'Participante asignado exitosamente.');
    }

    /**
     * Update reservation status
     */
    public function updateReservationStatus(Request $request, $reservationId)
    {
        $request->validate([
            'status' => 'required|in:confirmed,rejected,cancelled',
            'rejection_reason' => 'required_if:status,rejected',
        ]);

        $reservation = JobOfferReservation::with('jobOffer')->findOrFail($reservationId);
        $oldStatus = $reservation->status;

        DB::transaction(function() use ($reservation, $request, $oldStatus) {
            // Actualizar reserva
            $reservation->update([
                'status' => $request->status,
                'confirmed_at' => $request->status === 'confirmed' ? now() : null,
                'rejected_at' => $request->status === 'rejected' ? now() : null,
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Si se cancela o rechaza, liberar el cupo
            if (in_array($request->status, ['rejected', 'cancelled']) && in_array($oldStatus, ['pending', 'confirmed'])) {
                $offer = $reservation->jobOffer;
                $offer->increment('available_slots');
                
                // Si había estado como full, volver a available
                if ($offer->status === 'full' && $offer->available_slots > 0) {
                    $offer->update(['status' => 'available']);
                }
            }
        });

        $statusMessages = [
            'confirmed' => 'Reserva confirmada exitosamente.',
            'rejected' => 'Reserva rechazada. El participante ha sido notificado.',
            'cancelled' => 'Reserva cancelada. El cupo ha sido liberado.',
        ];

        return back()->with('success', $statusMessages[$request->status]);
    }

    /**
     * Show reservation history
     */
    public function reservationHistory($offerId)
    {
        $offer = JobOffer::with(['sponsor', 'hostCompany'])->findOrFail($offerId);
        
        $reservations = JobOfferReservation::with('user')
            ->where('job_offer_id', $offerId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.job-offers.history', compact('offer', 'reservations'));
    }
}
