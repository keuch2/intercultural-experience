<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Application;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Dashboard principal para usuario de finanzas
     */
    public function dashboard()
    {
        // Estadísticas financieras
        $stats = [
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'total_revenue' => Invoice::where('status', 'paid')->sum('total'),
            'pending_revenue' => Invoice::where('status', 'pending')->sum('total'),
        ];

        // Transacciones recientes
        $recentTransactions = FinancialTransaction::with(['user', 'application'])
            ->latest()
            ->take(10)
            ->get();

        // Facturas pendientes
        $pendingInvoices = Invoice::with(['user', 'application'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        // Ingresos por mes (últimos 6 meses)
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Monedas activas
        $currencies = Currency::where('is_active', true)->get();

        return view('finance.dashboard', compact(
            'stats',
            'recentTransactions',
            'pendingInvoices',
            'monthlyRevenue',
            'currencies'
        ));
    }
}
