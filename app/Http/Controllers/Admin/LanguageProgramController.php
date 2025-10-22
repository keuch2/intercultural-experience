<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LanguageProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LanguageProgramController extends Controller
{
    /**
     * Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_programs' => LanguageProgram::count(),
            'active_programs' => LanguageProgram::active()->count(),
            'completed_programs' => LanguageProgram::completed()->count(),
            'pending_programs' => LanguageProgram::pending()->count(),
        ];

        // Recent programs
        $recentPrograms = LanguageProgram::with(['user'])
            ->latest()
            ->take(10)
            ->get();

        // By language
        $byLanguage = LanguageProgram::select('language', DB::raw('count(*) as total'))
            ->groupBy('language')
            ->orderByDesc('total')
            ->get();

        // By program type
        $byProgramType = LanguageProgram::select('program_type', DB::raw('count(*) as total'))
            ->groupBy('program_type')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // By city
        $byCity = LanguageProgram::select('school_city', 'school_state', DB::raw('count(*) as total'))
            ->groupBy('school_city', 'school_state')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return view('admin.language-program.dashboard', compact(
            'stats',
            'recentPrograms',
            'byLanguage',
            'byProgramType',
            'byCity'
        ));
    }

    /**
     * Programs List
     */
    public function programs(Request $request)
    {
        $query = LanguageProgram::with(['user']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('program_type')) {
            $query->where('program_type', $request->program_type);
        }

        if ($request->filled('school_city')) {
            $query->where('school_city', $request->school_city);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('program_number', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $programs = $query->latest()->paginate(20);

        $cities = LanguageProgram::distinct()
            ->select('school_city', 'school_state')
            ->get()
            ->map(fn($p) => "{$p->school_city}, {$p->school_state}")
            ->unique();

        return view('admin.language-program.programs', compact('programs', 'cities'));
    }

    /**
     * Show Program Detail
     */
    public function showProgram($id)
    {
        $program = LanguageProgram::with(['user', 'processor'])
            ->findOrFail($id);

        return view('admin.language-program.program-show', compact('program'));
    }

    /**
     * Update Program Status
     */
    public function updateProgramStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:submitted,under_review,accepted,enrolled,active,completed,rejected,cancelled',
            'notes' => 'nullable|string',
        ]);

        $program = LanguageProgram::findOrFail($id);

        $program->updateStatus($request->status, $request->notes);
        $program->update(['processed_by' => auth()->id()]);

        return redirect()->route('admin.language-program.program.show', $program->id)
            ->with('success', 'Program status updated successfully');
    }

    /**
     * Update Progress
     */
    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'completed_weeks' => 'required|integer|min:0',
            'attendance_percentage' => 'nullable|integer|min:0|max:100',
            'teacher_feedback' => 'nullable|string',
            'progress_rating' => 'nullable|in:excellent,good,satisfactory,needs_improvement',
        ]);

        $program = LanguageProgram::findOrFail($id);

        $program->updateProgress(
            $request->completed_weeks,
            $request->teacher_feedback,
            $request->progress_rating
        );

        if ($request->filled('attendance_percentage')) {
            $program->recordAttendance($request->attendance_percentage);
        }

        return redirect()->route('admin.language-program.program.show', $program->id)
            ->with('success', 'Progress updated successfully');
    }

    /**
     * Issue Certificate
     */
    public function issueCertificate(Request $request, $id)
    {
        $request->validate([
            'certificate' => 'required|file|mimes:pdf',
        ]);

        $program = LanguageProgram::findOrFail($id);

        $path = $request->file('certificate')->store('language_certificates', 'public');

        $program->issueCertificate($path);

        return redirect()->route('admin.language-program.program.show', $program->id)
            ->with('success', 'Certificate issued successfully');
    }

    /**
     * Statistics
     */
    public function statistics()
    {
        $stats = [
            'total_programs' => LanguageProgram::count(),
            'active_programs' => LanguageProgram::active()->count(),
            'completed_programs' => LanguageProgram::completed()->count(),
            'total_students' => LanguageProgram::distinct('user_id')->count(),
            'avg_duration_weeks' => LanguageProgram::avg('weeks_duration'),
            'avg_hours_per_week' => LanguageProgram::avg('hours_per_week'),
        ];

        // By language detailed
        $languageStats = LanguageProgram::select(
            'language',
            DB::raw('count(*) as total'),
            DB::raw('avg(weeks_duration) as avg_weeks'),
            DB::raw('avg(hours_per_week) as avg_hours'),
            DB::raw('sum(total_program_cost) as total_revenue')
        )
        ->groupBy('language')
        ->orderByDesc('total')
        ->get();

        // By program type detailed
        $programTypeStats = LanguageProgram::select(
            'program_type',
            DB::raw('count(*) as total'),
            DB::raw('avg(total_program_cost) as avg_cost')
        )
        ->groupBy('program_type')
        ->orderByDesc('total')
        ->get();

        // By school
        $schoolStats = LanguageProgram::select(
            'school_name',
            'school_city',
            'school_state',
            DB::raw('count(*) as total_programs'),
            DB::raw('avg(weeks_duration) as avg_duration')
        )
        ->groupBy('school_name', 'school_city', 'school_state')
        ->orderByDesc('total_programs')
        ->take(10)
        ->get();

        // Monthly enrollment trend (last 12 months)
        $enrollmentTrend = LanguageProgram::select(
            DB::raw('DATE_FORMAT(start_date, "%Y-%m") as month'),
            DB::raw('count(*) as enrollments')
        )
        ->where('start_date', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return view('admin.language-program.statistics', compact(
            'stats',
            'languageStats',
            'programTypeStats',
            'schoolStats',
            'enrollmentTrend'
        ));
    }

    /**
     * Schools Report
     */
    public function schools()
    {
        $schools = LanguageProgram::select(
            'school_name',
            'school_city',
            'school_state',
            'school_country',
            'school_accreditation',
            DB::raw('count(*) as total_students'),
            DB::raw('count(distinct language) as languages_offered'),
            DB::raw('avg(hours_per_week) as avg_hours_weekly'),
            DB::raw('avg(total_program_cost) as avg_program_cost')
        )
        ->groupBy('school_name', 'school_city', 'school_state', 'school_country', 'school_accreditation')
        ->orderByDesc('total_students')
        ->paginate(20);

        return view('admin.language-program.schools', compact('schools'));
    }
}
