<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkTravelValidation;
use App\Models\WorkContract;
use App\Models\Employer;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkTravelController extends Controller
{
    /**
     * Display Work & Travel dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'total_participants' => WorkTravelValidation::count(),
            'validated_students' => WorkTravelValidation::validated()->count(),
            'presencial_validated' => WorkTravelValidation::presencial()->validated()->count(),
            'active_contracts' => WorkContract::active()->count(),
            'verified_employers' => Employer::verified()->count(),
            'available_positions' => Employer::active()->sum('positions_available'),
            'summer_participants' => WorkTravelValidation::summer()->count(),
            'winter_participants' => WorkTravelValidation::winter()->count(),
        ];

        // Recent validations
        $recentValidations = WorkTravelValidation::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Top employers
        $topEmployers = Employer::topRated()
            ->withAvailablePositions()
            ->take(5)
            ->get();

        // Contracts by status
        $contractsByStatus = WorkContract::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Monthly placements
        $monthlyPlacements = WorkContract::where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.work-travel.dashboard', compact(
            'stats',
            'recentValidations',
            'topEmployers',
            'contractsByStatus',
            'monthlyPlacements'
        ));
    }

    /**
     * Display list of validations.
     */
    public function validations(Request $request)
    {
        $query = WorkTravelValidation::with(['user', 'application']);

        // Filters
        if ($request->filled('status')) {
            $query->where('validation_status', $request->status);
        }

        if ($request->filled('study_type')) {
            $query->where('study_type', $request->study_type);
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('university_name', 'like', "%{$search}%");
        }

        $validations = $query->latest()->paginate(20);

        return view('admin.work-travel.validations', compact('validations'));
    }

    /**
     * Show validation details.
     */
    public function showValidation($id)
    {
        $validation = WorkTravelValidation::with(['user', 'application', 'validator'])
            ->findOrFail($id);

        return view('admin.work-travel.validation-show', compact('validation'));
    }

    /**
     * Validate a student's eligibility.
     */
    public function validateStudent(Request $request, $id)
    {
        $validation = WorkTravelValidation::findOrFail($id);

        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject',
            'is_presencial_validated' => 'boolean',
            'meets_age_requirement' => 'boolean',
            'meets_academic_requirement' => 'boolean',
            'meets_english_requirement' => 'boolean',
            'has_valid_passport' => 'boolean',
            'has_clean_record' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $validation) {
            if ($request->action === 'approve') {
                $validation->update([
                    'validation_status' => 'approved',
                    'is_presencial_validated' => $request->boolean('is_presencial_validated'),
                    'meets_age_requirement' => $request->boolean('meets_age_requirement'),
                    'meets_academic_requirement' => $request->boolean('meets_academic_requirement'),
                    'meets_english_requirement' => $request->boolean('meets_english_requirement'),
                    'has_valid_passport' => $request->boolean('has_valid_passport'),
                    'has_clean_record' => $request->boolean('has_clean_record'),
                    'validation_date' => now(),
                    'validated_by' => auth()->id(),
                ]);

                // Update application status if exists
                if ($validation->application) {
                    $validation->application->update(['status' => 'approved']);
                }
            } else {
                $validation->update([
                    'validation_status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                    'validated_by' => auth()->id(),
                ]);

                // Update application status if exists
                if ($validation->application) {
                    $validation->application->update(['status' => 'rejected']);
                }
            }
        });

        return redirect()->route('admin.work-travel.validations')
            ->with('success', 'Validation ' . $request->action . 'd successfully.');
    }

    /**
     * Display list of employers.
     */
    public function employers(Request $request)
    {
        $query = Employer::query();

        // Filters
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        if ($request->filled('season')) {
            $query->whereJsonContains('seasons_hiring', $request->season);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('business_type', 'like', "%{$search}%");
            });
        }

        $employers = $query->latest()->paginate(20);

        $states = Employer::distinct()->pluck('state')->sort();

        return view('admin.work-travel.employers', compact('employers', 'states'));
    }

    /**
     * Show employer details.
     */
    public function showEmployer($id)
    {
        $employer = Employer::with(['contracts.user', 'jobOffers'])
            ->findOrFail($id);

        $stats = [
            'active_contracts' => $employer->contracts()->active()->count(),
            'total_contracts' => $employer->contracts()->count(),
            'current_participants' => $employer->activeContracts()->count(),
        ];

        return view('admin.work-travel.employer-show', compact('employer', 'stats'));
    }

    /**
     * Create new employer.
     */
    public function createEmployer()
    {
        return view('admin.work-travel.employer-create');
    }

    /**
     * Store new employer.
     */
    public function storeEmployer(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'ein_number' => 'nullable|string|max:50',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'number_of_employees' => 'nullable|integer|min:1',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'zip_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_title' => 'required|string|max:100',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'positions_available' => 'nullable|integer|min:0',
            'job_categories' => 'nullable|array',
            'seasons_hiring' => 'nullable|array',
            'e_verify_enrolled' => 'boolean',
            'workers_comp_insurance' => 'boolean',
            'liability_insurance' => 'boolean',
        ]);

        $employer = Employer::create($validated);

        return redirect()->route('admin.work-travel.employer.show', $employer)
            ->with('success', 'Employer created successfully.');
    }

    /**
     * Verify an employer.
     */
    public function verifyEmployer($id)
    {
        $employer = Employer::findOrFail($id);
        $employer->verify();

        return back()->with('success', 'Employer verified successfully.');
    }

    /**
     * Display list of contracts.
     */
    public function contracts(Request $request)
    {
        $query = WorkContract::with(['user', 'employer']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('season')) {
            $season = $request->season;
            if ($season === 'summer') {
                $query->whereMonth('start_date', '>=', 5)
                      ->whereMonth('start_date', '<=', 8);
            } else {
                $query->whereMonth('start_date', '>=', 11)
                      ->orWhereMonth('start_date', '<=', 2);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhere('position_title', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('employer', function ($q3) use ($search) {
                      $q3->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $contracts = $query->latest()->paginate(20);

        return view('admin.work-travel.contracts', compact('contracts'));
    }

    /**
     * Show contract details.
     */
    public function showContract($id)
    {
        $contract = WorkContract::with(['user', 'employer', 'jobOffer', 'verifier'])
            ->findOrFail($id);

        return view('admin.work-travel.contract-show', compact('contract'));
    }

    /**
     * Verify a contract.
     */
    public function verifyContract(Request $request, $id)
    {
        $contract = WorkContract::findOrFail($id);

        $request->validate([
            'employer_verified' => 'required|boolean',
            'position_verified' => 'required|boolean',
        ]);

        $contract->update([
            'employer_verified' => $request->boolean('employer_verified'),
            'position_verified' => $request->boolean('position_verified'),
            'verified_by' => auth()->id(),
            'verification_date' => now(),
        ]);

        if ($contract->employer_verified && $contract->position_verified) {
            $contract->update(['status' => 'pending_signature']);
        }

        return back()->with('success', 'Contract verification updated successfully.');
    }

    /**
     * Generate contract matching suggestions.
     */
    public function matching(Request $request)
    {
        // Get validated students without contracts
        $students = WorkTravelValidation::approved()
            ->whereDoesntHave('user.workContracts', function ($q) {
                $q->whereIn('status', ['active', 'pending_signature']);
            })
            ->with('user')
            ->get();

        // Get employers with available positions
        $employers = Employer::active()
            ->verified()
            ->withAvailablePositions()
            ->get();

        // Calculate matches
        $matches = [];
        foreach ($students as $student) {
            foreach ($employers as $employer) {
                $score = $this->calculateMatchScore($student, $employer);
                if ($score > 50) { // Minimum threshold
                    $matches[] = [
                        'student' => $student,
                        'employer' => $employer,
                        'score' => $score,
                    ];
                }
            }
        }

        // Sort by score
        usort($matches, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        return view('admin.work-travel.matching', compact('matches', 'students', 'employers'));
    }

    /**
     * Calculate match score between student and employer.
     */
    private function calculateMatchScore($validation, $employer)
    {
        $score = 0;

        // Season match (30 points)
        if (in_array($validation->season, $employer->seasons_hiring ?? [])) {
            $score += 30;
        }

        // Location preference (20 points) - simplified for now
        $score += 20;

        // Availability match (25 points)
        if ($employer->positions_available > 0) {
            $score += 25;
        }

        // Employer rating bonus (15 points)
        if ($employer->rating >= 4.0) {
            $score += 15;
        }

        // Validation completeness (10 points)
        if ($validation->meetsAllRequirements()) {
            $score += 10;
        }

        return $score;
    }
}
