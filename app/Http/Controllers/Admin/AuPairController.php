<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuPairProfile;
use App\Models\FamilyProfile;
use App\Models\AuPairMatch;
use App\Models\ChildcareExperience;
use App\Models\Reference;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuPairController extends Controller
{
    /**
     * Dashboard con métricas del programa Au Pair
     */
    public function dashboard()
    {
        $stats = [
            'total_profiles' => AuPairProfile::count(),
            'active_profiles' => AuPairProfile::where('profile_status', 'active')->count(),
            'matched_profiles' => AuPairProfile::where('profile_status', 'matched')->count(),
            'pending_review' => AuPairProfile::where('profile_status', 'pending')->count(),
            'total_families' => FamilyProfile::count(),
            'active_matches' => AuPairMatch::where('is_matched', true)->count(),
            'pending_matches' => AuPairMatch::where('au_pair_status', 'pending')
                ->orWhere('family_status', 'pending')->count(),
        ];

        // Profiles incompletos
        $incompleteProfiles = AuPairProfile::where('profile_complete', false)
            ->with('user:id,name,email')
            ->latest()
            ->limit(10)
            ->get();

        // Matches recientes
        $recentMatches = AuPairMatch::where('is_matched', true)
            ->with(['auPairProfile.user', 'familyProfile'])
            ->latest('matched_at')
            ->limit(10)
            ->get();

        // Distribución por estado
        $profilesByStatus = AuPairProfile::select('profile_status', DB::raw('count(*) as count'))
            ->groupBy('profile_status')
            ->pluck('count', 'profile_status');

        return view('admin.au-pair.dashboard', compact(
            'stats',
            'incompleteProfiles',
            'recentMatches',
            'profilesByStatus'
        ));
    }

    /**
     * Lista de perfiles Au Pair
     */
    public function profiles(Request $request)
    {
        $query = AuPairProfile::with(['user', 'application.program']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('profile_status', $request->status);
        }

        if ($request->filled('complete')) {
            $query->where('profile_complete', $request->complete === 'yes');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $profiles = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.au-pair.profiles', compact('profiles'));
    }

    /**
     * Detalle de perfil Au Pair
     */
    public function profileShow($id)
    {
        $profile = AuPairProfile::with([
            'user.childcareExperiences',
            'user.references',
            'user.healthDeclaration',
            'user.emergencyContacts',
            'application.program',
            'matches.familyProfile'
        ])->findOrFail($id);

        // Calcular completitud del perfil
        $completeness = $this->calculateProfileCompleteness($profile);

        // Experiencia total con niños
        $totalChildcareMonths = $profile->user->childcareExperiences->sum('duration_months');

        // Referencias verificadas
        $verifiedReferences = $profile->user->references->where('verified', true)->count();

        return view('admin.au-pair.profile-show', compact(
            'profile',
            'completeness',
            'totalChildcareMonths',
            'verifiedReferences'
        ));
    }

    /**
     * Aprobar perfil Au Pair para matching
     */
    public function approveProfile($id)
    {
        $profile = AuPairProfile::findOrFail($id);

        // Validaciones
        if (!$this->validateProfileForApproval($profile)) {
            return back()->with('error', 'El perfil no cumple los requisitos mínimos para aprobación.');
        }

        $profile->update([
            'profile_status' => 'active',
            'profile_complete' => true
        ]);

        // Notificar al participante
        // TODO: Implementar notificación

        return back()->with('success', 'Perfil aprobado y activado para matching.');
    }

    /**
     * Lista de familias registradas
     */
    public function families(Request $request)
    {
        $query = FamilyProfile::query();

        // Filtros
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('has_infants')) {
            $query->where('has_infants', $request->has_infants === 'yes');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('family_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $families = $query->orderBy('created_at', 'desc')->paginate(15);

        // Estados únicos para filtro
        $states = FamilyProfile::distinct()->pluck('state')->sort();
        
        // Estadísticas
        $stats = [
            'active' => $families->count(),
            'matched' => FamilyProfile::whereHas('matches', function ($q) {
                $q->where('is_matched', true);
            })->count(),
            'with_infants' => FamilyProfile::where('has_infants', true)->count(),
            'states_count' => $states->count()
        ];

        return view('admin.au-pair.families', compact('families', 'states', 'stats'));
    }

    /**
     * Crear nueva familia
     */
    public function createFamily()
    {
        $states = [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming'
        ];

        return view('admin.au-pair.create-family', compact('states'));
    }

    /**
     * Guardar nueva familia
     */
    public function storeFamily(Request $request)
    {
        $validated = $request->validate([
            'family_name' => 'required|string|max:255',
            'parent1_name' => 'required|string|max:255',
            'parent2_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:family_profiles',
            'phone' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'number_of_children' => 'required|integer|min:1|max:10',
            'children_ages' => 'required|array',
            'children_ages.*' => 'integer|min:0|max:18',
            'has_infants' => 'boolean',
            'has_special_needs' => 'boolean',
            'special_needs_detail' => 'required_if:has_special_needs,true|nullable|string',
            'has_pets' => 'boolean',
            'pet_types' => 'required_if:has_pets,true|nullable|string',
            'smoking_household' => 'boolean',
            'required_gender' => 'in:female,male,any',
            'drivers_license_required' => 'boolean',
            'swimming_required' => 'boolean',
            'weekly_stipend' => 'numeric|min:195.75',
            'education_fund' => 'numeric|min:500',
            'additional_benefits' => 'nullable|string|max:1000',
        ]);

        $validated['has_infants'] = in_array(true, array_map(fn($age) => $age < 2, $validated['children_ages']));

        FamilyProfile::create($validated);

        return redirect()->route('admin.au-pair.families')
            ->with('success', 'Familia registrada exitosamente.');
    }

    /**
     * Sistema de matching - vista
     */
    public function matching(Request $request)
    {
        // Au Pairs activos para matching
        $auPairs = AuPairProfile::where('profile_status', 'active')
            ->with('user')
            ->get();

        // Familias buscando Au Pair
        $families = FamilyProfile::whereDoesntHave('matches', function ($q) {
            $q->where('is_matched', true);
        })->get();

        // Matches pendientes (no confirmados)
        $pendingMatches = AuPairMatch::where('is_matched', false)
            ->where(function ($q) {
                $q->where('au_pair_status', 'pending')
                    ->orWhere('family_status', 'pending');
            })
            ->with(['auPairProfile.user', 'familyProfile'])
            ->get();
        
        // Matches con interés mutuo
        $interestedMatches = AuPairMatch::where('is_matched', false)
            ->where('au_pair_status', 'interested')
            ->where('family_status', 'interested')
            ->with(['auPairProfile.user', 'familyProfile'])
            ->get();
        
        // Matches confirmados
        $confirmedMatches = AuPairMatch::where('is_matched', true)
            ->with(['auPairProfile.user', 'familyProfile'])
            ->orderBy('matched_at', 'desc')
            ->limit(10)
            ->get();
        
        // Matriz de compatibilidad (simplificada)
        $compatibilityMatrix = [];
        if ($request->has('au_pair') || $request->has('family')) {
            foreach ($auPairs as $auPair) {
                foreach ($families as $family) {
                    // Calcular compatibilidad básica
                    $compatibility = $this->calculateCompatibility($auPair, $family);
                    $compatibilityMatrix[$auPair->id][$family->id] = $compatibility;
                }
            }
        }
        
        // Estadísticas
        $stats = [
            'total_matches' => AuPairMatch::count(),
            'success_rate' => AuPairMatch::count() > 0 
                ? round((AuPairMatch::where('is_matched', true)->count() / AuPairMatch::count()) * 100, 1)
                : 0,
            'avg_matching_days' => 14, // TODO: Calcular real
            'in_process' => $pendingMatches->count() + $interestedMatches->count()
        ];

        return view('admin.au-pair.matching', compact(
            'auPairs',
            'families',
            'pendingMatches',
            'interestedMatches',
            'confirmedMatches',
            'compatibilityMatrix',
            'stats'
        ));
    }

    /**
     * Crear un match sugerido
     */
    public function suggestMatch(Request $request)
    {
        $validated = $request->validate([
            'au_pair_profile_id' => 'required|exists:au_pair_profiles,id',
            'family_profile_id' => 'required|exists:family_profiles,id',
        ]);

        // Verificar que no existe ya
        $exists = AuPairMatch::where('au_pair_profile_id', $validated['au_pair_profile_id'])
            ->where('family_profile_id', $validated['family_profile_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Este match ya existe.');
        }

        // Crear match pendiente
        AuPairMatch::create([
            'au_pair_profile_id' => $validated['au_pair_profile_id'],
            'family_profile_id' => $validated['family_profile_id'],
            'au_pair_status' => 'pending',
            'family_status' => 'pending',
            'is_matched' => false,
        ]);

        // TODO: Notificar a ambas partes

        return back()->with('success', 'Match sugerido creado. Esperando respuesta de ambas partes.');
    }

    /**
     * Confirmar un match
     */
    public function confirmMatch($id)
    {
        $match = AuPairMatch::findOrFail($id);

        // Verificar que ambas partes estén interesadas
        if ($match->au_pair_status !== 'interested' || $match->family_status !== 'interested') {
            return back()->with('error', 'Ambas partes deben estar interesadas para confirmar el match.');
        }

        DB::transaction(function () use ($match) {
            // Confirmar match
            $match->update([
                'is_matched' => true,
                'matched_at' => now(),
            ]);

            // Actualizar estado del perfil Au Pair
            $match->auPairProfile->update([
                'profile_status' => 'matched'
            ]);

            // TODO: Notificar a ambas partes
            // TODO: Iniciar proceso de documentación
        });

        return back()->with('success', 'Match confirmado exitosamente.');
    }

    /**
     * Vista de experiencia con niños
     */
    public function childcareExperiences($userId)
    {
        $user = User::with(['childcareExperiences', 'auPairProfile'])->findOrFail($userId);
        
        $experiences = $user->childcareExperiences;
        $totalMonths = $experiences->sum('duration_months');
        $hasInfantExperience = $experiences->where('cared_for_infants', true)->count() > 0;
        $hasSpecialNeedsExp = $experiences->where('special_needs_experience', true)->count() > 0;

        return view('admin.au-pair.childcare-experiences', compact(
            'user',
            'experiences',
            'totalMonths',
            'hasInfantExperience',
            'hasSpecialNeedsExp'
        ));
    }

    /**
     * Vista de referencias
     */
    public function references($userId)
    {
        $user = User::with('references')->findOrFail($userId);
        
        $childcareRefs = $user->references->where('reference_type', 'childcare');
        $characterRefs = $user->references->where('reference_type', 'character');
        $verifiedCount = $user->references->where('verified', true)->count();

        return view('admin.au-pair.references', compact(
            'user',
            'childcareRefs',
            'characterRefs',
            'verifiedCount'
        ));
    }

    /**
     * Verificar una referencia
     */
    public function verifyReference($id)
    {
        $reference = Reference::findOrFail($id);
        
        $reference->update([
            'verified' => true,
            'verification_date' => now(),
        ]);

        return back()->with('success', 'Referencia verificada exitosamente.');
    }

    /**
     * Calcular completitud del perfil
     */
    private function calculateProfileCompleteness(AuPairProfile $profile): array
    {
        $checks = [
            'photos' => !empty($profile->photos) && count($profile->photos) >= 6,
            'video' => !empty($profile->video_presentation),
            'dear_family_letter' => !empty($profile->dear_family_letter),
            'childcare_experience' => $profile->user->childcareExperiences->count() > 0,
            'references' => $profile->user->references->count() >= 3,
            'health_declaration' => $profile->user->healthDeclaration !== null,
            'emergency_contacts' => $profile->user->emergencyContacts->count() >= 2,
        ];

        $completed = array_filter($checks);
        $percentage = (count($completed) / count($checks)) * 100;

        return [
            'checks' => $checks,
            'completed' => count($completed),
            'total' => count($checks),
            'percentage' => round($percentage),
        ];
    }

    /**
     * Validar perfil para aprobación
     */
    private function validateProfileForApproval(AuPairProfile $profile): bool
    {
        // Requisitos mínimos
        if (empty($profile->photos) || count($profile->photos) < 6) {
            return false;
        }

        if (empty($profile->video_presentation)) {
            return false;
        }

        if (empty($profile->dear_family_letter)) {
            return false;
        }

        if ($profile->user->childcareExperiences->count() === 0) {
            return false;
        }

        if ($profile->user->references->count() < 3) {
            return false;
        }

        if (!$profile->user->healthDeclaration) {
            return false;
        }

        return true;
    }

    /**
     * Estadísticas de matching
     */
    public function matchingStats()
    {
        $stats = [
            'total_matches' => AuPairMatch::count(),
            'successful_matches' => AuPairMatch::where('is_matched', true)->count(),
            'pending_matches' => AuPairMatch::where('is_matched', false)->count(),
            'rejection_rate' => $this->calculateRejectionRate(),
            'avg_time_to_match' => $this->calculateAvgTimeToMatch(),
            'matches_by_state' => $this->getMatchesByState(),
        ];

        return view('admin.au-pair.matching-stats', compact('stats'));
    }

    /**
     * Calcular compatibilidad entre Au Pair y Familia
     */
    private function calculateCompatibility($auPair, $family)
    {
        $score = 0;
        $factors = 0;
        
        // Factor 1: Experiencia con bebés si la familia tiene infantes
        if ($family->has_infants) {
            $factors++;
            if ($auPair->user->childcareExperiences()->where('cared_for_infants', true)->exists()) {
                $score += 20;
            }
        }
        
        // Factor 2: Licencia de conducir
        if ($family->drivers_license_required) {
            $factors++;
            if ($auPair->user->has_drivers_license) {
                $score += 15;
            }
        }
        
        // Factor 3: Saber nadar
        if ($family->swimming_required) {
            $factors++;
            if ($auPair->user->can_swim) {
                $score += 15;
            }
        }
        
        // Factor 4: Género preferido
        if ($family->required_gender != 'any') {
            $factors++;
            if ($auPair->user->gender == $family->required_gender) {
                $score += 10;
            }
        }
        
        // Factor 5: No fumador si la familia no fuma
        if (!$family->smoking_household) {
            $factors++;
            if (!$auPair->user->smoker) {
                $score += 10;
            }
        }
        
        // Factor 6: Experiencia con necesidades especiales
        if ($family->has_special_needs) {
            $factors++;
            if ($auPair->user->childcareExperiences()->where('special_needs_experience', true)->exists()) {
                $score += 20;
            }
        }
        
        // Factor 7: Número de niños
        $factors++;
        if ($auPair->max_children >= $family->number_of_children) {
            $score += 10;
        }
        
        // Base score para todos
        $score += 20;
        
        return $factors > 0 ? round($score / ($factors + 1) * 100 / 20) : 50;
    }
    
    /**
     * Calcular tasa de rechazo
     */
    private function calculateRejectionRate()
    {
        $total = AuPairMatch::count();
        if ($total == 0) return 0;
        
        $rejected = AuPairMatch::where('au_pair_status', 'not_interested')
            ->orWhere('family_status', 'not_interested')
            ->count();

        return round(($rejected / $total) * 100, 2);
    }

    private function calculateAvgTimeToMatch()
    {
        $matches = AuPairMatch::where('is_matched', true)
            ->whereNotNull('matched_at')
            ->get();

        if ($matches->count() === 0) return 0;

        $totalDays = 0;
        foreach ($matches as $match) {
            $totalDays += $match->created_at->diffInDays($match->matched_at);
        }

        return round($totalDays / $matches->count(), 1);
    }

    private function getMatchesByState()
    {
        return DB::table('au_pair_matches')
            ->join('family_profiles', 'au_pair_matches.family_profile_id', '=', 'family_profiles.id')
            ->where('au_pair_matches.is_matched', true)
            ->select('family_profiles.state', DB::raw('count(*) as count'))
            ->groupBy('family_profiles.state')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }
}
