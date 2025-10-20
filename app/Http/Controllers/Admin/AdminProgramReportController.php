<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\User;
use App\Models\Application;
use App\Models\ProgramAssignment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminProgramReportController extends Controller
{
    /**
     * Dashboard de reportes de programas
     */
    public function index()
    {
        $totalPrograms = Program::count();
        $activePrograms = Program::where('is_active', true)->count();
        $totalUsers = User::where('role', 'user')->count();
        $totalApplications = Application::count();
        $totalAssignments = ProgramAssignment::count();
        
        // Programas con más asignaciones
        $topProgramsByAssignments = Program::withCount('assignments')
            ->orderBy('assignments_count', 'desc')
            ->take(5)
            ->get();
            
        // Usuarios con más asignaciones
        $topUsersByAssignments = User::withCount('programAssignments')
            ->where('role', 'user')
            ->orderBy('program_assignments_count', 'desc')
            ->take(5)
            ->get();
            
        // Estados de asignaciones
        $assignmentStats = ProgramAssignment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.reports.programs.index', compact(
            'totalPrograms', 'activePrograms', 'totalUsers', 'totalApplications',
            'totalAssignments', 'topProgramsByAssignments', 'topUsersByAssignments',
            'assignmentStats'
        ));
    }

    /**
     * Reporte detallado de un programa específico
     */
    public function programDetail(Program $program)
    {
        $program->load(['currency', 'assignments.user', 'applications.user']);
        
        // Estadísticas del programa
        $stats = [
            'total_assignments' => $program->assignments()->count(),
            'pending_assignments' => $program->assignments()->where('status', 'assigned')->count(),
            'applied_assignments' => $program->assignments()->where('status', 'applied')->count(),
            'accepted_assignments' => $program->assignments()->where('status', 'accepted')->count(),
            'completed_assignments' => $program->assignments()->where('status', 'completed')->count(),
            'overdue_assignments' => $program->assignments()
                ->where('application_deadline', '<', Carbon::now())
                ->where('status', 'assigned')
                ->count(),
            'total_applications' => $program->applications()->count(),
            'pending_applications' => $program->applications()->where('status', 'pending')->count(),
            'approved_applications' => $program->applications()->where('status', 'approved')->count(),
            'rejected_applications' => $program->applications()->where('status', 'rejected')->count(),
        ];
        
        // Progreso por mes (últimos 6 meses)
        $monthlyProgress = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthName = $date->format('M Y');
            
            $monthlyProgress[$monthName] = [
                'assignments' => $program->assignments()
                    ->whereYear('assigned_at', $date->year)
                    ->whereMonth('assigned_at', $date->month)
                    ->count(),
                'applications' => $program->applications()
                    ->whereYear('applied_at', $date->year)
                    ->whereMonth('applied_at', $date->month)
                    ->count(),
            ];
        }

        // Usuarios por estado
        $usersByStatus = $program->assignments()
            ->with('user')
            ->get()
            ->groupBy('status')
            ->map(function ($assignments) {
                return $assignments->map(function ($assignment) {
                    return [
                        'user' => $assignment->user,
                        'assignment' => $assignment,
                        'progress_percentage' => $assignment->progress_percentage,
                        'days_since_assignment' => $assignment->assigned_at->diffInDays(Carbon::now()),
                    ];
                });
            });

        return view('admin.reports.programs.detail', compact(
            'program', 'stats', 'monthlyProgress', 'usersByStatus'
        ));
    }

    /**
     * Reporte de estado de todos los usuarios
     */
    public function usersOverview(Request $request)
    {
        $query = User::with(['programAssignments.program', 'applications.program'])
            ->where('role', 'user');

        // Filtros
        if ($request->filled('status_filter')) {
            $statusFilter = $request->status_filter;
            $query->whereHas('programAssignments', function($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            });
        }

        if ($request->filled('program_filter')) {
            $query->whereHas('programAssignments', function($q) use ($request) {
                $q->where('program_id', $request->program_filter);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        // Agregar estadísticas calculadas para cada usuario
        $users->getCollection()->transform(function ($user) {
            $assignments = $user->programAssignments;
            $applications = $user->applications;
            
            $user->stats = [
                'total_assignments' => $assignments->count(),
                'active_assignments' => $assignments->where('status', 'assigned')->count(),
                'completed_assignments' => $assignments->where('status', 'completed')->count(),
                'overdue_assignments' => $assignments->filter(function($assignment) {
                    return $assignment->is_overdue;
                })->count(),
                'total_applications' => $applications->count(),
                'approved_applications' => $applications->where('status', 'approved')->count(),
                'latest_activity' => $assignments->max('updated_at') ?: $applications->max('updated_at'),
                'average_progress' => $assignments->avg('progress_percentage') ?: 0,
            ];
            
            return $user;
        });

        $programs = Program::active()->orderBy('name')->get();
        $statuses = ProgramAssignment::STATUSES;

        return view('admin.reports.users.overview', compact(
            'users', 'programs', 'statuses'
        ));
    }

    /**
     * Reporte detallado de un usuario específico
     */
    public function userDetail(User $user)
    {
        $user->load([
            'programAssignments.program', 
            'applications.program',
            'points',
            'redemptions.reward'
        ]);

        // Estadísticas del usuario
        $stats = [
            'total_assignments' => $user->programAssignments()->count(),
            'active_assignments' => $user->programAssignments()->active()->count(),
            'completed_assignments' => $user->programAssignments()->where('status', 'completed')->count(),
            'total_applications' => $user->applications()->count(),
            'approved_applications' => $user->applications()->where('status', 'approved')->count(),
            'total_points' => $user->points()->sum('change'),
            'points_used' => $user->redemptions()->sum('points_cost'),
            'points_balance' => $user->points()->sum('change') - $user->redemptions()->sum('points_cost'),
        ];

        // Timeline de actividades
        $timeline = collect();

        // Agregar asignaciones
        foreach ($user->programAssignments as $assignment) {
            $timeline->push([
                'type' => 'assignment',
                'date' => $assignment->assigned_at,
                'title' => 'Programa Asignado',
                'description' => "Asignado al programa: {$assignment->program->name}",
                'status' => $assignment->status,
                'data' => $assignment
            ]);
        }

        // Agregar aplicaciones
        foreach ($user->applications as $application) {
            $timeline->push([
                'type' => 'application',
                'date' => $application->applied_at,
                'title' => 'Aplicación Enviada',
                'description' => "Aplicó al programa: {$application->program->name}",
                'status' => $application->status,
                'data' => $application
            ]);
        }

        $timeline = $timeline->sortByDesc('date')->take(20);

        return view('admin.reports.users.detail', compact(
            'user', 'stats', 'timeline'
        ));
    }

    /**
     * Exportar reporte de programa a Excel
     */
    public function exportProgram(Program $program)
    {
        $data = $this->getProgramReportData($program);
        
        // Aquí implementarías la exportación a Excel
        // Por ahora retornamos JSON para testing
        return response()->json([
            'success' => true,
            'message' => 'Exportación preparada',
            'data' => $data
        ]);
    }

    /**
     * Exportar reporte de usuarios a Excel
     */
    public function exportUsers(Request $request)
    {
        $data = $this->getUsersReportData($request);
        
        return response()->json([
            'success' => true,
            'message' => 'Exportación de usuarios preparada',
            'data' => $data
        ]);
    }

    /**
     * Obtener datos del reporte de programa
     */
    private function getProgramReportData(Program $program)
    {
        return [
            'program' => $program->toArray(),
            'assignments' => $program->assignments()->with('user')->get()->toArray(),
            'applications' => $program->applications()->with('user')->get()->toArray(),
            'generated_at' => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Obtener datos del reporte de usuarios
     */
    private function getUsersReportData(Request $request)
    {
        $users = User::with(['programAssignments.program', 'applications.program'])
            ->where('role', 'user')
            ->get();

        return [
            'users' => $users->toArray(),
            'filters' => $request->all(),
            'generated_at' => Carbon::now()->toISOString(),
        ];
    }
}
