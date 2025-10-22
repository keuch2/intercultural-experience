<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\HigherEducationApplication;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HigherEducationController extends Controller
{
    /**
     * Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_applications' => HigherEducationApplication::count(),
            'pending_applications' => HigherEducationApplication::pending()->count(),
            'accepted_applications' => HigherEducationApplication::accepted()->count(),
            'universities' => University::active()->count(),
            'partner_universities' => University::partner()->count(),
            'total_scholarships' => Scholarship::count(),
            'active_scholarships' => Scholarship::active()->count(),
            'scholarship_applications' => ScholarshipApplication::count(),
            'awarded_scholarships' => ScholarshipApplication::awarded()->count(),
        ];

        // Recent applications
        $recentApplications = HigherEducationApplication::with(['user', 'university'])
            ->latest()
            ->take(10)
            ->get();

        // By degree level
        $byDegreeLevel = HigherEducationApplication::select('degree_level', DB::raw('count(*) as total'))
            ->groupBy('degree_level')
            ->get();

        // Top universities
        $topUniversities = University::withCount('applications')
            ->orderByDesc('applications_count')
            ->take(10)
            ->get();

        return view('admin.higher-education.dashboard', compact(
            'stats',
            'recentApplications',
            'byDegreeLevel',
            'topUniversities'
        ));
    }

    /**
     * Universities List
     */
    public function universities(Request $request)
    {
        $query = University::query();

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('partner')) {
            $query->where('is_partner_university', $request->boolean('partner'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('university_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $universities = $query->withCount('applications', 'scholarships')
            ->latest()
            ->paginate(20);

        $states = University::distinct()->pluck('state')->filter();

        return view('admin.higher-education.universities', compact('universities', 'states'));
    }

    /**
     * Show University Detail
     */
    public function showUniversity($id)
    {
        $university = University::with(['applications', 'scholarships'])
            ->findOrFail($id);

        $stats = [
            'total_applications' => $university->applications()->count(),
            'pending' => $university->applications()->pending()->count(),
            'accepted' => $university->applications()->accepted()->count(),
            'current_students' => $university->students_current,
        ];

        return view('admin.higher-education.university-show', compact('university', 'stats'));
    }

    /**
     * Applications List
     */
    public function applications(Request $request)
    {
        $query = HigherEducationApplication::with(['user', 'university']);

        // Filters
        if ($request->filled('status')) {
            $query->where('application_status', $request->status);
        }

        if ($request->filled('degree_level')) {
            $query->where('degree_level', $request->degree_level);
        }

        if ($request->filled('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $query->latest()->paginate(20);

        $universities = University::active()->pluck('university_name', 'id');

        return view('admin.higher-education.applications', compact('applications', 'universities'));
    }

    /**
     * Show Application Detail
     */
    public function showApplication($id)
    {
        $application = HigherEducationApplication::with([
            'user',
            'university',
            'processor',
            'scholarshipApplications.scholarship'
        ])->findOrFail($id);

        return view('admin.higher-education.application-show', compact('application'));
    }

    /**
     * Update Application Status
     */
    public function updateApplicationStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:submitted,under_review,additional_docs_required,accepted,conditionally_accepted,waitlisted,rejected,deferred',
            'decision_notes' => 'nullable|string',
            'is_conditional' => 'nullable|boolean',
            'conditions' => 'nullable|array',
        ]);

        $application = HigherEducationApplication::findOrFail($id);

        $application->updateStatus($request->status, $request->decision_notes);

        if ($request->boolean('is_conditional')) {
            $application->update([
                'is_conditional' => true,
                'conditions' => $request->conditions,
                'conditions_deadline' => $request->conditions_deadline,
            ]);
        }

        $application->update(['processed_by' => auth()->id()]);

        return redirect()->route('admin.higher-education.application.show', $application->id)
            ->with('success', 'Application status updated successfully');
    }

    /**
     * Issue I-20
     */
    public function issueI20(Request $request, $id)
    {
        $request->validate([
            'sevis_id' => 'required|string',
            'i20_document' => 'required|file|mimes:pdf',
        ]);

        $application = HigherEducationApplication::findOrFail($id);

        $path = $request->file('i20_document')->store('i20_documents', 'public');

        $application->issueI20($request->sevis_id, $path);

        return redirect()->route('admin.higher-education.application.show', $application->id)
            ->with('success', 'I-20 issued successfully');
    }

    /**
     * Scholarships List
     */
    public function scholarships(Request $request)
    {
        $query = Scholarship::with('university');

        // Filters
        if ($request->filled('type')) {
            $query->where('scholarship_type', $request->type);
        }

        if ($request->filled('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        if ($request->filled('active')) {
            if ($request->boolean('active')) {
                $query->active();
            }
        }

        $scholarships = $query->latest()->paginate(20);

        $universities = University::active()->pluck('university_name', 'id');

        return view('admin.higher-education.scholarships', compact('scholarships', 'universities'));
    }

    /**
     * Show Scholarship Detail
     */
    public function showScholarship($id)
    {
        $scholarship = Scholarship::with(['university', 'applications'])
            ->findOrFail($id);

        $stats = [
            'total_applications' => $scholarship->applications()->count(),
            'pending' => $scholarship->applications()->pending()->count(),
            'awarded' => $scholarship->applications()->awarded()->count(),
            'awards_remaining' => $scholarship->awards_remaining,
        ];

        return view('admin.higher-education.scholarship-show', compact('scholarship', 'stats'));
    }

    /**
     * Scholarship Applications List
     */
    public function scholarshipApplications(Request $request)
    {
        $query = ScholarshipApplication::with(['user', 'scholarship', 'universityApplication']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scholarship_id')) {
            $query->where('scholarship_id', $request->scholarship_id);
        }

        if ($request->filled('awarded')) {
            $query->where('is_awarded', $request->boolean('awarded'));
        }

        $applications = $query->latest()->paginate(20);

        $scholarships = Scholarship::active()->pluck('scholarship_name', 'id');

        return view('admin.higher-education.scholarship-applications', compact('applications', 'scholarships'));
    }

    /**
     * Award Scholarship
     */
    public function awardScholarship(Request $request, $id)
    {
        $request->validate([
            'awarded_amount' => 'required|numeric|min:0',
            'award_start_date' => 'required|date',
            'award_end_date' => 'required|date|after:award_start_date',
        ]);

        $application = ScholarshipApplication::findOrFail($id);

        $application->awardScholarship(
            $request->awarded_amount,
            $request->award_start_date,
            $request->award_end_date
        );

        $application->update(['reviewed_by' => auth()->id()]);

        return redirect()->back()
            ->with('success', 'Scholarship awarded successfully');
    }

    /**
     * Matching System
     */
    public function matching(Request $request)
    {
        $query = HigherEducationApplication::with(['user', 'university'])
            ->whereIn('application_status', ['submitted', 'under_review']);

        // Get all active scholarships
        $scholarships = Scholarship::active()->get();

        if ($request->filled('degree_level')) {
            $query->where('degree_level', $request->degree_level);
        }

        $applications = $query->paginate(20);

        // Calculate scholarship matches for each application
        foreach ($applications as $application) {
            $application->scholarship_matches = $this->calculateScholarshipMatches($application, $scholarships);
        }

        return view('admin.higher-education.matching', compact('applications', 'scholarships'));
    }

    /**
     * Calculate Scholarship Matches
     */
    private function calculateScholarshipMatches($application, $scholarships)
    {
        $matches = [];

        foreach ($scholarships as $scholarship) {
            $score = 0;

            // GPA match (40 points)
            if ($scholarship->min_gpa_required) {
                if ($application->gpa >= $scholarship->min_gpa_required) {
                    $score += 40;
                } else {
                    continue; // Skip if doesn't meet minimum GPA
                }
            } else {
                $score += 30;
            }

            // Degree level match (30 points)
            if (!$scholarship->eligible_degree_levels || 
                in_array($application->degree_level, $scholarship->eligible_degree_levels)) {
                $score += 30;
            }

            // University match (20 points)
            if ($scholarship->university_id === $application->university_id) {
                $score += 20;
            }

            // Financial need (10 points)
            if ($scholarship->scholarship_type === 'need_based' && $application->needs_financial_aid) {
                $score += 10;
            }

            if ($score >= 60) { // Only show matches above 60%
                $matches[] = [
                    'scholarship' => $scholarship,
                    'score' => $score,
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
