<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\User;
use App\Models\Reward;
use App\Models\Redemption;
use App\Models\Program;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Muestra el informe de solicitudes.
     *
     * @return \Illuminate\Http\Response
     */
    public function applications(Request $request)
    {
        // Filtrar por fecha si es necesario
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(6);
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        
        // Obtener datos para el gráfico de solicitudes por estado
        $applicationsByStatus = Application::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        
        // Obtener datos para el gráfico de solicitudes por programa
        $applicationsByProgram = Application::whereBetween('created_at', [$startDate, $endDate])
            ->select('program_id', DB::raw('count(*) as total'))
            ->groupBy('program_id')
            ->with('program')
            ->get()
            ->map(function ($item) {
                return [
                    'program' => $item->program->name,
                    'total' => $item->total
                ];
            });
        
        // Obtener datos para el gráfico de solicitudes por mes
        $applicationsByMonth = Application::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M Y'),
                    'total' => $item->total
                ];
            });
        
        // Obtener datos para la tabla de solicitudes
        $applications = Application::with(['user', 'program'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Obtener programas para el filtro
        $programs = Program::all();
        
        return view('admin.reports.applications', compact(
            'applicationsByStatus', 
            'applicationsByProgram', 
            'applicationsByMonth', 
            'applications', 
            'programs', 
            'startDate', 
            'endDate'
        ));
    }

    /**
     * Muestra el informe de usuarios.
     *
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {
        // Filtrar por fecha si es necesario
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(6);
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        
        // Obtener datos para el gráfico de usuarios por nacionalidad
        $usersByCountry = User::whereBetween('created_at', [$startDate, $endDate])
            ->select('nationality', DB::raw('count(*) as total'))
            ->groupBy('nationality')
            ->get();
        
        // Obtener datos para el gráfico de usuarios por mes
        $usersByMonth = User::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M Y'),
                    'total' => $item->total
                ];
            });
        
        // Obtener datos para el gráfico de usuarios por rol
        $usersByRole = User::whereBetween('created_at', [$startDate, $endDate])
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get();
        
        // Obtener datos para la tabla de usuarios
        $users = User::whereBetween('created_at', [$startDate, $endDate])
            ->withCount(['applications', 'redemptions'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Obtener usuarios más activos (con más solicitudes)
        $mostActiveUsers = User::withCount('applications')
            ->orderBy('applications_count', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.reports.users', compact(
            'usersByCountry', 
            'usersByMonth', 
            'usersByRole', 
            'users', 
            'mostActiveUsers', 
            'startDate', 
            'endDate'
        ));
    }

    /**
     * Muestra el informe de recompensas.
     *
     * @return \Illuminate\Http\Response
     */
    public function rewards(Request $request)
    {
        // Filtrar por fecha si es necesario
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(6);
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        
        // Obtener datos para el gráfico de canjes por recompensa
        $redemptionsByReward = Redemption::whereBetween('created_at', [$startDate, $endDate])
            ->select('reward_id', DB::raw('count(*) as total'))
            ->groupBy('reward_id')
            ->with('reward')
            ->get()
            ->map(function ($item) {
                return [
                    'reward' => $item->reward->name,
                    'total' => $item->total
                ];
            });
        
        // Obtener datos para el gráfico de canjes por mes
        $redemptionsByMonth = Redemption::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M Y'),
                    'total' => $item->total
                ];
            });
        
        // Obtener datos para el gráfico de canjes por estado
        $redemptionsByStatus = Redemption::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        
        // Obtener datos para la tabla de recompensas
        $rewards = Reward::withCount('redemptions')
            ->orderBy('redemptions_count', 'desc')
            ->paginate(10);
        
        // Obtener datos para la tabla de canjes recientes
        $recentRedemptions = Redemption::with(['user', 'reward'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.reports.rewards', compact(
            'redemptionsByReward', 
            'redemptionsByMonth', 
            'redemptionsByStatus', 
            'rewards', 
            'recentRedemptions', 
            'startDate', 
            'endDate'
        ));
    }

    /**
     * Muestra el dashboard de reportes financieros
     */
    public function index()
    {
        // Obtener estadísticas generales
        $stats = $this->getGeneralStats();
        
        // Obtener datos para gráficos del año actual
        $currentYear = Carbon::now()->year;
        $monthlyData = $this->getMonthlyRevenueData($currentYear);
        $programStats = $this->getProgramStats();
        $currencyStats = $this->getCurrencyStats();
        
        return view('admin.reports.index', compact(
            'stats', 
            'monthlyData', 
            'programStats', 
            'currencyStats',
            'currentYear'
        ));
    }

    /**
     * Reporte detallado de ingresos por programas
     */
    public function programs(Request $request)
    {
        $query = Program::with(['currency', 'applications' => function($q) {
            $q->where('status', 'approved');
        }]);

        // Filtros
        if ($request->filled('year')) {
            $year = $request->input('year');
            $query->whereHas('applications', function($q) use ($year) {
                $q->where('status', 'approved')
                  ->whereYear('created_at', $year);
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->input('currency_id'));
        }

        $programs = $query->get();

        // Calcular ingresos para cada programa
        $programsWithRevenue = $programs->map(function ($program) use ($request) {
            $approvedApplications = $program->applications->where('status', 'approved');
            
            // Filtrar por año si se especifica
            if ($request->filled('year')) {
                $year = $request->input('year');
                $approvedApplications = $approvedApplications->filter(function($app) use ($year) {
                    return $app->created_at->year == $year;
                });
            }

            $participantsCount = $approvedApplications->count();
            $revenueInOriginalCurrency = $participantsCount * $program->cost;
            $revenueInPyg = $program->currency ? 
                $program->currency->convertToPyg($revenueInOriginalCurrency) : 
                $revenueInOriginalCurrency;

            return [
                'program' => $program,
                'participants_count' => $participantsCount,
                'revenue_original' => $revenueInOriginalCurrency,
                'revenue_pyg' => $revenueInPyg,
                'currency_code' => $program->currency ? $program->currency->code : 'PYG',
                'currency_symbol' => $program->currency ? $program->currency->symbol : '₲'
            ];
        })->sortByDesc('revenue_pyg');

        // Totales
        $totalRevenuePyg = $programsWithRevenue->sum('revenue_pyg');
        $totalParticipants = $programsWithRevenue->sum('participants_count');

        // Datos para filtros
        $years = Application::where('status', 'approved')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $categories = Program::distinct('category')->pluck('category');
        $countries = Program::distinct('country')->orderBy('country')->pluck('country');
        $currencies = Currency::active()->orderBy('code')->get();

        return view('admin.reports.programs', compact(
            'programsWithRevenue',
            'totalRevenuePyg',
            'totalParticipants',
            'years',
            'categories',
            'countries',
            'currencies',
            'request'
        ));
    }

    /**
     * Reporte de ingresos por moneda
     */
    public function currencies(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        $currencyRevenue = Currency::with(['programs.applications' => function($q) use ($year) {
            $q->where('status', 'approved')
              ->whereYear('created_at', $year);
        }])->get()->map(function ($currency) {
            $totalRevenue = 0;
            $totalParticipants = 0;
            
            foreach ($currency->programs as $program) {
                $approvedApps = $program->applications->where('status', 'approved');
                $participants = $approvedApps->count();
                $revenue = $participants * $program->cost;
                
                $totalRevenue += $revenue;
                $totalParticipants += $participants;
            }
            
            return [
                'currency' => $currency,
                'revenue_original' => $totalRevenue,
                'revenue_pyg' => $currency->convertToPyg($totalRevenue),
                'participants_count' => $totalParticipants,
                'programs_count' => $currency->programs()->count()
            ];
        })->filter(function($item) {
            return $item['participants_count'] > 0;
        })->sortByDesc('revenue_pyg');

        $totalRevenuePyg = $currencyRevenue->sum('revenue_pyg');
        $totalParticipants = $currencyRevenue->sum('participants_count');

        $years = Application::where('status', 'approved')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('admin.reports.currencies', compact(
            'currencyRevenue',
            'totalRevenuePyg',
            'totalParticipants',
            'years',
            'year'
        ));
    }

    /**
     * Reporte mensual detallado
     */
    public function monthly(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Aplicaciones aprobadas en el mes
        $applications = Application::with(['program.currency', 'user'])
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Agrupar por programa
        $programRevenue = $applications->groupBy('program_id')->map(function ($apps, $programId) {
            $program = $apps->first()->program;
            $participantsCount = $apps->count();
            $revenueOriginal = $participantsCount * $program->cost;
            $revenuePyg = $program->currency ? 
                $program->currency->convertToPyg($revenueOriginal) : 
                $revenueOriginal;

            return [
                'program' => $program,
                'participants_count' => $participantsCount,
                'revenue_original' => $revenueOriginal,
                'revenue_pyg' => $revenuePyg,
                'applications' => $apps
            ];
        })->sortByDesc('revenue_pyg');

        // Estadísticas del mes
        $monthStats = [
            'total_revenue_pyg' => $programRevenue->sum('revenue_pyg'),
            'total_participants' => $programRevenue->sum('participants_count'),
            'total_programs' => $programRevenue->count(),
            'applications_count' => $applications->count()
        ];

        // Comparación con mes anterior
        $prevMonth = $startDate->copy()->subMonth();
        $prevMonthApps = Application::where('status', 'approved')
            ->whereBetween('created_at', [$prevMonth->startOfMonth(), $prevMonth->endOfMonth()])
            ->with('program.currency')
            ->get();

        $prevMonthRevenue = $prevMonthApps->sum(function($app) {
            return $app->program->currency ? 
                $app->program->currency->convertToPyg($app->program->cost) : 
                $app->program->cost;
        });

        $revenueGrowth = $prevMonthRevenue > 0 ? 
            (($monthStats['total_revenue_pyg'] - $prevMonthRevenue) / $prevMonthRevenue) * 100 : 
            0;

        $years = Application::where('status', 'approved')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('admin.reports.monthly', compact(
            'programRevenue',
            'monthStats',
            'revenueGrowth',
            'prevMonthRevenue',
            'year',
            'month',
            'years',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Exportar reporte a Excel/CSV
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'programs');
        $format = $request->input('format', 'csv');
        
        switch ($type) {
            case 'programs':
                return $this->exportProgramsReport($request, $format);
            case 'currencies':
                return $this->exportCurrenciesReport($request, $format);
            case 'monthly':
                return $this->exportMonthlyReport($request, $format);
            default:
                return redirect()->back()->with('error', 'Tipo de reporte no válido');
        }
    }

    // Métodos privados para obtener estadísticas

    private function getGeneralStats()
    {
        $currentYear = Carbon::now()->year;
        
        // Ingresos totales del año en PYG
        $totalRevenuePyg = Application::where('status', 'approved')
            ->whereYear('created_at', $currentYear)
            ->with('program.currency')
            ->get()
            ->sum(function($application) {
                return $application->program->currency ? 
                    $application->program->currency->convertToPyg($application->program->cost) : 
                    $application->program->cost;
            });

        // Participantes del año
        $totalParticipants = Application::where('status', 'approved')
            ->whereYear('created_at', $currentYear)
            ->count();

        // Programas activos con ingresos
        $activeProgramsWithRevenue = Program::whereHas('applications', function($q) use ($currentYear) {
            $q->where('status', 'approved')
              ->whereYear('created_at', $currentYear);
        })->count();

        // Ingreso promedio por participante
        $avgRevenuePerParticipant = $totalParticipants > 0 ? 
            $totalRevenuePyg / $totalParticipants : 0;

        return [
            'total_revenue_pyg' => $totalRevenuePyg,
            'total_participants' => $totalParticipants,
            'active_programs_with_revenue' => $activeProgramsWithRevenue,
            'avg_revenue_per_participant' => $avgRevenuePerParticipant
        ];
    }

    private function getMonthlyRevenueData($year)
    {
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $revenue = Application::where('status', 'approved')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->with('program.currency')
                ->get()
                ->sum(function($application) {
                    return $application->program->currency ? 
                        $application->program->currency->convertToPyg($application->program->cost) : 
                        $application->program->cost;
                });

            $monthlyData[] = [
                'month' => $month,
                'month_name' => Carbon::create($year, $month, 1)->format('M'),
                'revenue_pyg' => $revenue
            ];
        }

        return $monthlyData;
    }

    private function getProgramStats()
    {
        return Program::select('category', DB::raw('COUNT(*) as count'))
            ->whereHas('applications', function($q) {
                $q->where('status', 'approved');
            })
            ->groupBy('category')
            ->get();
    }

    private function getCurrencyStats()
    {
        return Currency::with(['programs' => function($q) {
            $q->whereHas('applications', function($subQ) {
                $subQ->where('status', 'approved');
            });
        }])->get()->map(function($currency) {
            $revenue = 0;
            foreach ($currency->programs as $program) {
                $participants = $program->applications()->where('status', 'approved')->count();
                $revenue += $participants * $program->cost;
            }
            
            return [
                'currency' => $currency,
                'revenue_original' => $revenue,
                'revenue_pyg' => $currency->convertToPyg($revenue)
            ];
        })->filter(function($item) {
            return $item['revenue_original'] > 0;
        });
    }

    private function exportProgramsReport($request, $format)
    {
        // Implementar exportación de programas
        // Por ahora retornamos un mensaje
        return redirect()->back()->with('success', 'Funcionalidad de exportación en desarrollo');
    }

    private function exportCurrenciesReport($request, $format)
    {
        // Implementar exportación de monedas
        return redirect()->back()->with('success', 'Funcionalidad de exportación en desarrollo');
    }

    private function exportMonthlyReport($request, $format)
    {
        // Implementar exportación mensual
        return redirect()->back()->with('success', 'Funcionalidad de exportación en desarrollo');
    }
}
