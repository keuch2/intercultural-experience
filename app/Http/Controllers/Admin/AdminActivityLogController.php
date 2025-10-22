<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminActivityLogController extends Controller
{
    /**
     * Display activity logs dashboard
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['causer'])->latest('created_at');

        // Filtros
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50);

        // Datos para los filtros
        $logNames = ActivityLog::distinct()->pluck('log_name')->filter()->sort();
        $actions = ActivityLog::distinct()->pluck('action')->filter()->sort();
        $subjectTypes = ActivityLog::distinct()->pluck('subject_type')->filter()->sort();
        $causers = User::where('role', 'admin')->orderBy('name')->get();

        // Estadísticas
        $stats = [
            'total_logs' => ActivityLog::count(),
            'today_logs' => ActivityLog::whereDate('created_at', Carbon::today())->count(),
            'week_logs' => ActivityLog::where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'unique_users' => ActivityLog::distinct('causer_id')->count('causer_id'),
        ];

        return view('admin.activity-logs.index', compact(
            'logs', 'logNames', 'actions', 'subjectTypes', 'causers', 'stats'
        ));
    }

    /**
     * Show detailed activity log
     */
    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['causer', 'subject']);
        
        return view('admin.activity-logs.show', compact('activityLog'));
    }

    /**
     * Get activity logs for a specific model via AJAX
     */
    public function getModelLogs(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $logs = ActivityLog::where('subject_type', $request->model_type)
            ->where('subject_id', $request->model_id)
            ->with('causer')
            ->latest('created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs->map(function($log) {
                return [
                    'id' => $log->id,
                    'description' => $log->formatted_description,
                    'action' => $log->action,
                    'created_at' => $log->created_at->diffForHumans(),
                    'causer' => $log->causer ? $log->causer->name : 'Sistema',
                    'changes' => $log->changes,
                    'properties' => $log->properties,
                ];
            })
        ]);
    }

    /**
     * Activity logs statistics API
     */
    public function stats(Request $request)
    {
        $period = $request->get('period', 'week'); // day, week, month, year

        switch ($period) {
            case 'day':
                $startDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                break;
            default:
                $startDate = Carbon::now()->startOfWeek();
        }

        // Actividad por día
        $dailyActivity = ActivityLog::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Actividad por tipo de log
        $activityByType = ActivityLog::where('created_at', '>=', $startDate)
            ->selectRaw('log_name, COUNT(*) as count')
            ->groupBy('log_name')
            ->orderBy('count', 'desc')
            ->get();

        // Actividad por acción
        $activityByAction = ActivityLog::where('created_at', '>=', $startDate)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        // Usuarios más activos
        $topUsers = ActivityLog::where('created_at', '>=', $startDate)
            ->whereNotNull('causer_id')
            ->with('causer:id,name')
            ->selectRaw('causer_id, COUNT(*) as count')
            ->groupBy('causer_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'period' => $period,
            'start_date' => $startDate->toISOString(),
            'stats' => [
                'daily_activity' => $dailyActivity,
                'activity_by_type' => $activityByType,
                'activity_by_action' => $activityByAction,
                'top_users' => $topUsers,
                'total_logs' => ActivityLog::where('created_at', '>=', $startDate)->count(),
            ]
        ]);
    }

    /**
     * Clear old activity logs
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365',
        ]);

        $cutoffDate = Carbon::now()->subDays($request->days);
        $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deletedCount} registros de actividad anteriores a {$cutoffDate->format('Y-m-d')}",
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with(['causer', 'subject'])->latest('created_at');

        // Aplicar filtros si existen
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->limit(10000)->get(); // Límite para evitar timeout

        return response()->json([
            'success' => true,
            'message' => 'Exportación preparada',
            'count' => $logs->count(),
            'data' => $logs->map(function($log) {
                return [
                    'id' => $log->id,
                    'log_name' => $log->log_name,
                    'description' => $log->description,
                    'action' => $log->action,
                    'causer' => $log->causer ? $log->causer->name : null,
                    'subject_type' => $log->subject_type,
                    'subject_id' => $log->subject_id,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at->toISOString(),
                ];
            })
        ]);
    }
}
