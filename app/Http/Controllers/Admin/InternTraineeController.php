<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternTraineeValidation;
use App\Models\HostCompany;
use App\Models\TrainingPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternTraineeController extends Controller
{
    /**
     * Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_participants' => InternTraineeValidation::count(),
            'interns' => InternTraineeValidation::interns()->count(),
            'trainees' => InternTraineeValidation::trainees()->count(),
            'pending_validations' => InternTraineeValidation::pending()->count(),
            'approved' => InternTraineeValidation::approved()->count(),
            'active_plans' => TrainingPlan::active()->count(),
            'completed_plans' => TrainingPlan::completed()->count(),
            'host_companies' => HostCompany::active()->count(),
            'avg_duration' => InternTraineeValidation::avg('duration_months') ?? 0,
        ];

        // Recent validations
        $recentValidations = InternTraineeValidation::with(['user', 'hostCompany'])
            ->latest()
            ->take(10)
            ->get();

        // Active training plans
        $activePlans = TrainingPlan::with(['user', 'hostCompany'])
            ->active()
            ->latest()
            ->take(10)
            ->get();

        // By industry
        $byIndustry = InternTraineeValidation::select('industry_sector', DB::raw('count(*) as total'))
            ->groupBy('industry_sector')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return view('admin.intern-trainee.dashboard', compact('stats', 'recentValidations', 'activePlans', 'byIndustry'));
    }

    /**
     * Validations List
     */
    public function validations(Request $request)
    {
        $query = InternTraineeValidation::with(['user', 'hostCompany', 'validator']);

        // Filters
        if ($request->filled('program_type')) {
            $query->where('program_type', $request->program_type);
        }

        if ($request->filled('status')) {
            $query->where('validation_status', $request->status);
        }

        if ($request->filled('industry')) {
            $query->where('industry_sector', $request->industry);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $validations = $query->latest()->paginate(20);

        // Filters data
        $industries = InternTraineeValidation::distinct()->pluck('industry_sector')->filter();

        return view('admin.intern-trainee.validations', compact('validations', 'industries'));
    }

    /**
     * Show Validation Detail
     */
    public function showValidation($id)
    {
        $validation = InternTraineeValidation::with(['user', 'hostCompany', 'validator', 'trainingPlan'])
            ->findOrFail($id);

        return view('admin.intern-trainee.validation-show', compact('validation'));
    }

    /**
     * Validate Intern/Trainee
     */
    public function validateParticipant(Request $request, $id)
    {
        $validation = InternTraineeValidation::findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject',
            'meets_age_requirement' => 'nullable|boolean',
            'meets_education_requirement' => 'nullable|boolean',
            'meets_experience_requirement' => 'nullable|boolean',
            'meets_english_requirement' => 'nullable|boolean',
            'has_valid_passport' => 'nullable|boolean',
            'has_clean_record' => 'nullable|boolean',
        ]);

        $validation->update([
            'meets_age_requirement' => $request->boolean('meets_age_requirement'),
            'meets_education_requirement' => $request->boolean('meets_education_requirement'),
            'meets_experience_requirement' => $request->boolean('meets_experience_requirement'),
            'meets_english_requirement' => $request->boolean('meets_english_requirement'),
            'has_valid_passport' => $request->boolean('has_valid_passport'),
            'has_clean_record' => $request->boolean('has_clean_record'),
            'validation_status' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'validated_by' => auth()->id(),
            'validation_completed_at' => now(),
        ]);

        return redirect()->route('admin.intern-trainee.validation.show', $validation->id)
            ->with('success', 'Validación procesada exitosamente');
    }

    /**
     * Host Companies List
     */
    public function companies(Request $request)
    {
        $query = HostCompany::query();

        // Filters
        if ($request->filled('industry')) {
            $query->where('industry_sector', $request->industry);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $companies = $query->withCount('internTraineeValidations', 'trainingPlans')
            ->latest()
            ->paginate(20);

        // Filters data
        $industries = HostCompany::distinct()->pluck('industry_sector')->filter();
        $states = HostCompany::distinct()->pluck('state')->filter();

        return view('admin.intern-trainee.companies', compact('companies', 'industries', 'states'));
    }

    /**
     * Show Company Detail
     */
    public function showCompany($id)
    {
        $company = HostCompany::with(['internTraineeValidations', 'trainingPlans'])
            ->findOrFail($id);

        $stats = [
            'active_plans' => $company->trainingPlans()->active()->count(),
            'total_plans' => $company->trainingPlans()->count(),
            'current_participants' => $company->current_participants,
            'positions_available' => $company->positions_available,
        ];

        return view('admin.intern-trainee.company-show', compact('company', 'stats'));
    }

    /**
     * Verify Company
     */
    public function verifyCompany($id)
    {
        $company = HostCompany::findOrFail($id);

        $company->update([
            'is_verified' => true,
            'verification_date' => now(),
            'verified_by' => auth()->id(),
        ]);

        return redirect()->route('admin.intern-trainee.company.show', $company->id)
            ->with('success', 'Empresa verificada exitosamente');
    }

    /**
     * Training Plans List
     */
    public function trainingPlans(Request $request)
    {
        $query = TrainingPlan::with(['user', 'hostCompany']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('program_type')) {
            $query->where('program_type', $request->program_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plan_title', 'like', "%{$search}%")
                  ->orWhere('position_title', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $plans = $query->latest()->paginate(20);

        return view('admin.intern-trainee.training-plans', compact('plans'));
    }

    /**
     * Show Training Plan Detail
     */
    public function showTrainingPlan($id)
    {
        $plan = TrainingPlan::with(['user', 'hostCompany', 'validation', 'companyApprover', 'sponsorApprover'])
            ->findOrFail($id);

        return view('admin.intern-trainee.plan-show', compact('plan'));
    }

    /**
     * Approve Training Plan (Sponsor)
     */
    public function approvePlan(Request $request, $id)
    {
        $plan = TrainingPlan::findOrFail($id);

        $plan->approveBySponsor(auth()->id());

        return redirect()->route('admin.intern-trainee.plan.show', $plan->id)
            ->with('success', 'Plan de entrenamiento aprobado');
    }

    /**
     * Terminate Training Plan
     */
    public function terminatePlan(Request $request, $id)
    {
        $request->validate([
            'termination_reason' => 'required|string',
        ]);

        $plan = TrainingPlan::findOrFail($id);
        $plan->terminate($request->termination_reason, auth()->id());

        return redirect()->route('admin.intern-trainee.plan.show', $plan->id)
            ->with('success', 'Plan terminado');
    }

    /**
     * Matching System
     */
    public function matching(Request $request)
    {
        $query = InternTraineeValidation::with(['user', 'hostCompany'])
            ->approved();

        // Available companies
        $companies = HostCompany::active()
            ->where('is_verified', true)
            ->where('positions_available', '>', 0)
            ->withCount('trainingPlans')
            ->get();

        // Filters
        if ($request->filled('program_type')) {
            $query->where('program_type', $request->program_type);
        }

        if ($request->filled('industry')) {
            $query->where('industry_sector', $request->industry);
        }

        $participants = $query->paginate(20);

        // Calculate matches for each participant
        foreach ($participants as $participant) {
            $participant->matches = $this->calculateMatches($participant, $companies);
        }

        return view('admin.intern-trainee.matching', compact('participants', 'companies'));
    }

    /**
     * Calculate Matching Score
     */
    private function calculateMatches($participant, $companies)
    {
        $matches = [];

        foreach ($companies as $company) {
            $score = 0;

            // Industry match (30 points)
            if ($participant->industry_sector === $company->industry_sector) {
                $score += 30;
            }

            // Required skills match (25 points)
            if ($company->required_skills && $participant->technical_skills) {
                $commonSkills = array_intersect($company->required_skills, $participant->technical_skills);
                $score += (count($commonSkills) / max(count($company->required_skills), 1)) * 25;
            }

            // Experience requirement match (20 points)
            if ($participant->program_type === 'trainee') {
                if ($participant->years_verified >= ($company->minimum_experience_years ?? 0)) {
                    $score += 20;
                }
            } else {
                // For interns, education match
                $score += 15;
            }

            // Location preference (15 points)
            if ($participant->preferred_states && in_array($company->state, $participant->preferred_states)) {
                $score += 15;
            }

            // Duration match (10 points)
            if ($participant->duration_months >= $company->min_duration_months && 
                $participant->duration_months <= $company->max_duration_months) {
                $score += 10;
            }

            if ($score >= 50) { // Only show matches above 50%
                $matches[] = [
                    'company' => $company,
                    'score' => round($score),
                ];
            }
        }

        // Sort by score
        usort($matches, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        return collect($matches)->take(5); // Top 5 matches
    }
}
