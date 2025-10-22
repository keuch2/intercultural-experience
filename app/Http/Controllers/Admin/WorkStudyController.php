<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkStudyProgram;
use App\Models\WorkStudyEmployer;
use App\Models\WorkStudyPlacement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkStudyController extends Controller
{
    /**
     * Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_programs' => WorkStudyProgram::count(),
            'active_programs' => WorkStudyProgram::active()->count(),
            'pending_programs' => WorkStudyProgram::pending()->count(),
            'total_employers' => WorkStudyEmployer::count(),
            'active_employers' => WorkStudyEmployer::active()->count(),
            'verified_employers' => WorkStudyEmployer::verified()->count(),
            'total_placements' => WorkStudyPlacement::count(),
            'active_placements' => WorkStudyPlacement::active()->count(),
        ];

        // Recent programs
        $recentPrograms = WorkStudyProgram::with(['user', 'employer'])
            ->latest()
            ->take(10)
            ->get();

        // By school city
        $byCity = WorkStudyProgram::select('school_city', DB::raw('count(*) as total'))
            ->groupBy('school_city')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Active placements
        $activePlacements = WorkStudyPlacement::with(['user', 'employer'])
            ->active()
            ->latest()
            ->take(10)
            ->get();

        return view('admin.work-study.dashboard', compact(
            'stats',
            'recentPrograms',
            'byCity',
            'activePlacements'
        ));
    }

    /**
     * Programs List
     */
    public function programs(Request $request)
    {
        $query = WorkStudyProgram::with(['user', 'employer']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('school_city')) {
            $query->where('school_city', $request->school_city);
        }

        if ($request->filled('program_type')) {
            $query->where('program_type', $request->program_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('program_number', 'like', "%{$search}%")
                  ->orWhere('language_school_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $programs = $query->latest()->paginate(20);

        $cities = WorkStudyProgram::distinct()->pluck('school_city')->filter();

        return view('admin.work-study.programs', compact('programs', 'cities'));
    }

    /**
     * Show Program Detail
     */
    public function showProgram($id)
    {
        $program = WorkStudyProgram::with(['user', 'employer', 'placement', 'processor'])
            ->findOrFail($id);

        return view('admin.work-study.program-show', compact('program'));
    }

    /**
     * Update Program Status
     */
    public function updateProgramStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:submitted,under_review,accepted,i20_issued,visa_approved,active,completed,rejected,cancelled',
            'notes' => 'nullable|string',
        ]);

        $program = WorkStudyProgram::findOrFail($id);

        $program->updateStatus($request->status, $request->notes);
        $program->update(['processed_by' => auth()->id()]);

        return redirect()->route('admin.work-study.program.show', $program->id)
            ->with('success', 'Program status updated successfully');
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

        $program = WorkStudyProgram::findOrFail($id);

        $path = $request->file('i20_document')->store('work_study_i20', 'public');

        $program->issueI20($request->sevis_id, $path);

        return redirect()->route('admin.work-study.program.show', $program->id)
            ->with('success', 'I-20 issued successfully');
    }

    /**
     * Employers List
     */
    public function employers(Request $request)
    {
        $query = WorkStudyEmployer::query();

        // Filters
        if ($request->filled('business_type')) {
            $query->where('business_type', $request->business_type);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        if ($request->filled('available_positions')) {
            $query->hasAvailablePositions();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('employer_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $employers = $query->withCount('placements')
            ->latest()
            ->paginate(20);

        $cities = WorkStudyEmployer::distinct()->pluck('city')->filter();

        return view('admin.work-study.employers', compact('employers', 'cities'));
    }

    /**
     * Show Employer Detail
     */
    public function showEmployer($id)
    {
        $employer = WorkStudyEmployer::with(['placements', 'programs'])
            ->findOrFail($id);

        $stats = [
            'total_placements' => $employer->placements()->count(),
            'active_placements' => $employer->placements()->active()->count(),
            'completed_placements' => $employer->placements()->completed()->count(),
            'available_positions' => $employer->available_positions_count,
        ];

        return view('admin.work-study.employer-show', compact('employer', 'stats'));
    }

    /**
     * Verify Employer
     */
    public function verifyEmployer($id)
    {
        $employer = WorkStudyEmployer::findOrFail($id);

        $employer->update([
            'is_verified' => true,
            'verification_date' => now(),
            'verified_by' => auth()->id(),
        ]);

        return redirect()->route('admin.work-study.employer.show', $employer->id)
            ->with('success', 'Employer verified successfully');
    }

    /**
     * Placements List
     */
    public function placements(Request $request)
    {
        $query = WorkStudyPlacement::with(['user', 'employer', 'program']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employer_id')) {
            $query->where('employer_id', $request->employer_id);
        }

        $placements = $query->latest()->paginate(20);

        $employers = WorkStudyEmployer::active()->pluck('employer_name', 'id');

        return view('admin.work-study.placements', compact('placements', 'employers'));
    }

    /**
     * Show Placement Detail
     */
    public function showPlacement($id)
    {
        $placement = WorkStudyPlacement::with([
            'user',
            'employer',
            'program',
            'processor'
        ])->findOrFail($id);

        return view('admin.work-study.placement-show', compact('placement'));
    }

    /**
     * Activate Placement
     */
    public function activatePlacement($id)
    {
        $placement = WorkStudyPlacement::findOrFail($id);

        if ($placement->status === 'approved') {
            $placement->activate();
            $placement->update(['processed_by' => auth()->id()]);

            return redirect()->route('admin.work-study.placement.show', $placement->id)
                ->with('success', 'Placement activated successfully');
        }

        return redirect()->back()->with('error', 'Placement must be approved first');
    }

    /**
     * Complete Placement
     */
    public function completePlacement($id)
    {
        $placement = WorkStudyPlacement::findOrFail($id);

        if ($placement->status === 'active') {
            $placement->complete();

            return redirect()->route('admin.work-study.placement.show', $placement->id)
                ->with('success', 'Placement completed successfully');
        }

        return redirect()->back()->with('error', 'Only active placements can be completed');
    }

    /**
     * Terminate Placement
     */
    public function terminatePlacement(Request $request, $id)
    {
        $request->validate([
            'termination_reason' => 'required|string',
        ]);

        $placement = WorkStudyPlacement::findOrFail($id);

        $placement->terminate($request->termination_reason, auth()->id());

        return redirect()->route('admin.work-study.placement.show', $placement->id)
            ->with('success', 'Placement terminated');
    }

    /**
     * Matching System
     */
    public function matching(Request $request)
    {
        $query = WorkStudyProgram::with(['user'])
            ->whereIn('status', ['accepted', 'i20_issued'])
            ->whereNull('employer_id');

        if ($request->filled('work_category')) {
            $query->where('work_category', $request->work_category);
        }

        if ($request->filled('school_city')) {
            $query->where('school_city', $request->school_city);
        }

        $programs = $query->paginate(20);

        // Get available employers
        $employers = WorkStudyEmployer::active()
            ->verified()
            ->hasAvailablePositions()
            ->get();

        // Calculate matches for each program
        foreach ($programs as $program) {
            $program->employer_matches = $this->calculateMatches($program, $employers);
        }

        return view('admin.work-study.matching', compact('programs', 'employers'));
    }

    /**
     * Calculate Employer Matches
     */
    private function calculateMatches($program, $employers)
    {
        $matches = [];

        foreach ($employers as $employer) {
            $score = 0;

            // Location match (30 points)
            if ($employer->city === $program->school_city) {
                $score += 30;
            } elseif ($employer->state === $program->school_state) {
                $score += 15;
            }

            // Work category match (30 points)
            if ($program->work_category) {
                $categoryMatch = match($program->work_category) {
                    'hospitality' => in_array($employer->business_type, ['hotel', 'resort']),
                    'food_service' => in_array($employer->business_type, ['restaurant', 'cafe']),
                    'retail' => $employer->business_type === 'retail_store',
                    default => false,
                };
                
                if ($categoryMatch) {
                    $score += 30;
                }
            } else {
                $score += 15; // Neutral if no preference
            }

            // Wage match (20 points)
            if ($program->expected_hourly_wage && $employer->hourly_wage_max) {
                if ($employer->hourly_wage_max >= $program->expected_hourly_wage) {
                    $score += 20;
                } else {
                    $score += 10;
                }
            } else {
                $score += 15;
            }

            // Available positions (10 points)
            if ($employer->available_positions_count > 0) {
                $score += 10;
            }

            // Rating (10 points)
            if ($employer->rating >= 4.5) {
                $score += 10;
            } elseif ($employer->rating >= 4.0) {
                $score += 7;
            } elseif ($employer->rating >= 3.5) {
                $score += 5;
            }

            if ($score >= 50) { // Only show matches above 50%
                $matches[] = [
                    'employer' => $employer,
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
