<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use App\Models\ProgramRequisite;
use App\Models\UserProgramRequisite;
use App\Models\Currency;
use App\Models\FinancialTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    /**
     * Muestra el panel financiero principal
     */
    public function index(Request $request)
    {
        // Obtener estadísticas financieras basadas en aplicaciones aprobadas
        $currentYear = Carbon::now()->year;
        
        // Ingresos totales del año en PYG (basado en aplicaciones aprobadas)
        $totalRevenuePyg = Application::where('status', 'approved')
            ->whereYear('created_at', $currentYear)
            ->with('program.currency')
            ->get()
            ->sum(function($application) {
                return $application->program->currency ? 
                    $application->program->currency->convertToPyg($application->program->cost) : 
                    $application->program->cost;
            });

        // Ingresos pendientes (aplicaciones pendientes)
        $pendingRevenuePyg = Application::where('status', 'pending')
            ->with('program.currency')
            ->get()
            ->sum(function($application) {
                return $application->program->currency ? 
                    $application->program->currency->convertToPyg($application->program->cost) : 
                    $application->program->cost;
            });

        // Ingresos mensuales del año actual en PYG
        $monthlyRevenue = [];
        for ($month = 1; $month <= 12; $month++) {
            $revenue = Application::where('status', 'approved')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->with('program.currency')
                ->get()
                ->sum(function($application) {
                    return $application->program->currency ? 
                        $application->program->currency->convertToPyg($application->program->cost) : 
                        $application->program->cost;
                });
            $monthlyRevenue[] = $revenue;
        }

        // Ingresos por programa en PYG
        $programRevenue = Program::with(['currency', 'applications' => function($q) use ($currentYear) {
            $q->where('status', 'approved')->whereYear('created_at', $currentYear);
        }])->get()->map(function($program) {
            $participants = $program->applications->count();
            $revenueOriginal = $participants * $program->cost;
            $revenuePyg = $program->currency ? 
                $program->currency->convertToPyg($revenueOriginal) : 
                $revenueOriginal;
            
            return [
                'name' => $program->name,
                'participants' => $participants,
                'revenue_original' => $revenueOriginal,
                'revenue_pyg' => $revenuePyg,
                'currency_code' => $program->currency ? $program->currency->code : 'PYG',
                'currency_symbol' => $program->currency ? $program->currency->symbol : '₲'
            ];
        })->filter(function($item) {
            return $item['participants'] > 0;
        })->sortByDesc('revenue_pyg')->take(10);

        // Estadísticas adicionales
        $totalParticipants = Application::where('status', 'approved')
            ->whereYear('created_at', $currentYear)
            ->count();

        $activeProgramsWithRevenue = Program::whereHas('applications', function($q) use ($currentYear) {
            $q->where('status', 'approved')->whereYear('created_at', $currentYear);
        })->count();

        // Distribución por monedas
        $currencyDistribution = Currency::with(['programs.applications' => function($q) use ($currentYear) {
            $q->where('status', 'approved')->whereYear('created_at', $currentYear);
        }])->get()->map(function($currency) {
            $revenue = 0;
            $participants = 0;
            
            foreach ($currency->programs as $program) {
                $programParticipants = $program->applications->count();
                $participants += $programParticipants;
                $revenue += $programParticipants * $program->cost;
            }
            
            return [
                'currency' => $currency,
                'revenue_original' => $revenue,
                'revenue_pyg' => $currency->convertToPyg($revenue),
                'participants' => $participants
            ];
        })->filter(function($item) {
            return $item['participants'] > 0;
        })->sortByDesc('revenue_pyg');
            
        return view('admin.finance.index', compact(
            'totalRevenuePyg', 
            'pendingRevenuePyg', 
            'monthlyRevenue',
            'programRevenue',
            'totalParticipants',
            'activeProgramsWithRevenue',
            'currencyDistribution',
            'currentYear'
        ));
    }
    
    /**
     * Muestra todos los pagos registrados
     */
    public function payments(Request $request)
    {
        $query = DB::table('user_program_requisites')
            ->join('program_requisites', 'user_program_requisites.program_requisite_id', '=', 'program_requisites.id')
            ->join('programs', 'program_requisites.program_id', '=', 'programs.id')
            ->join('applications', 'user_program_requisites.application_id', '=', 'applications.id')
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->leftJoin('currencies', 'programs.currency_id', '=', 'currencies.id')
            ->where('program_requisites.type', 'payment')
            ->select(
                'user_program_requisites.*', 
                'program_requisites.name as requisite_name',
                'programs.name as program_name',
                'users.name as user_name',
                'users.email',
                'user_program_requisites.observations as amount',
                'user_program_requisites.file_path as payment_reference',
                'currencies.symbol as currency_symbol',
                'currencies.code as currency_code'
            );
        
        // Filtros
        if ($request->has('status') && $request->status != '') {
            $query->where('user_program_requisites.status', $request->status);
        }
        
        if ($request->has('program_id') && $request->program_id != '') {
            $query->where('programs.id', $request->program_id);
        }
        
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('user_program_requisites.created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('user_program_requisites.created_at', '<=', $request->date_to);
        }
        
        $payments = $query->orderBy('user_program_requisites.created_at', 'desc')
            ->paginate(15);
            
        $programs = Program::orderBy('name')->get();
        
        return view('admin.finance.payments', compact('payments', 'programs'));
    }
    
    /**
     * Muestra el formulario para registrar un nuevo pago manual
     */
    public function createPayment()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        $programs = Program::with('currency')->orderBy('name')->get();
        $requisites = [];
        
        return view('admin.finance.create_payment', compact('users', 'programs', 'requisites'));
    }
    
    /**
     * Obtiene los requisitos de pago para un programa específico
     */
    public function getPaymentRequisites(Request $request)
    {
        $requisites = ProgramRequisite::where('program_id', $request->program_id)
            ->where('type', 'payment')
            ->get();
            
        $program = Program::with('currency')->find($request->program_id);
        
        return response()->json([
            'requisites' => $requisites,
            'program' => $program
        ]);
    }
    
    /**
     * Almacena un nuevo pago manual
     */
    public function storePayment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'program_requisite_id' => 'required|exists:program_requisites,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'reference' => 'required|string',
        ]);
        
        // Verificar si el usuario tiene una aplicación para el programa
        $programRequisite = ProgramRequisite::findOrFail($request->program_requisite_id);
        $application = Application::where('user_id', $request->user_id)
            ->where('program_id', $programRequisite->program_id)
            ->first();
            
        if (!$application) {
            // Crear una nueva aplicación si no existe
            $application = new Application();
            $application->user_id = $request->user_id;
            $application->program_id = $programRequisite->program_id;
            $application->status = 'pending';
            $application->save();
        }
        
        // Verificar si ya existe un registro para este usuario y requisito
        $existingPayment = UserProgramRequisite::where('application_id', $application->id)
            ->where('program_requisite_id', $request->program_requisite_id)
            ->first();
            
        if ($existingPayment) {
            $existingPayment->observations = $request->amount; // Guardar monto en observations
            $existingPayment->file_path = $request->reference; // Guardar referencia en file_path
            $existingPayment->status = 'verified';
            $existingPayment->verified_at = now();
            $existingPayment->save();
            
            return redirect()->route('admin.finance.payments')
                ->with('success', 'Pago actualizado correctamente.');
        } else {
            // Crear nuevo registro de pago
            $payment = new UserProgramRequisite();
            $payment->application_id = $application->id;
            $payment->program_requisite_id = $request->program_requisite_id;
            $payment->observations = $request->amount; // Guardar monto en observations
            $payment->file_path = $request->reference; // Guardar referencia en file_path
            $payment->status = 'verified';
            $payment->verified_at = now();
            $payment->save();
            
            return redirect()->route('admin.finance.payments')
                ->with('success', 'Pago registrado correctamente.');
        }
    }
    
    /**
     * Verifica un pago pendiente
     */
    public function verifyPayment(Request $request, $id)
    {
        $payment = UserProgramRequisite::findOrFail($id);
        $payment->status = 'verified';
        $payment->verified_at = now();
        $payment->save();
        
        return redirect()->back()->with('success', 'Pago verificado correctamente.');
    }
    
    /**
     * Rechaza un pago pendiente
     */
    public function rejectPayment(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);
        
        $payment = UserProgramRequisite::findOrFail($id);
        $payment->status = 'rejected';
        $payment->observations = $request->rejection_reason; // Guardar razón de rechazo en observations
        $payment->save();
        
        return redirect()->back()->with('success', 'Pago rechazado correctamente.');
    }
    
    /**
     * Muestra el informe financiero
     */
    public function report(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        
        // Obtener años con aplicaciones aprobadas
        $years = Application::where('status', 'approved')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
            
        if ($years->isEmpty()) {
            $years = collect([Carbon::now()->year]);
        }
            
        // Ingresos mensuales para el año seleccionado en PYG
        $monthlyData = [];
        $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
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
                
            $monthlyData[$month] = [
                'name' => $monthNames[$month-1],
                'total' => $revenue
            ];
        }
        
        // Ingresos por programa para el año seleccionado en PYG
        $programRevenue = Program::with(['currency', 'applications' => function($q) use ($year) {
            $q->where('status', 'approved')->whereYear('created_at', $year);
        }])->get()->map(function($program) {
            $participants = $program->applications->count();
            $revenueOriginal = $participants * $program->cost;
            $revenuePyg = $program->currency ? 
                $program->currency->convertToPyg($revenueOriginal) : 
                $revenueOriginal;
            
            return [
                'name' => $program->name,
                'participants' => $participants,
                'revenue_original' => $revenueOriginal,
                'revenue_pyg' => $revenuePyg,
                'currency_code' => $program->currency ? $program->currency->code : 'PYG',
                'currency_symbol' => $program->currency ? $program->currency->symbol : '₲'
            ];
        })->filter(function($item) {
            return $item['participants'] > 0;
        })->sortByDesc('revenue_pyg');

        // Distribución por monedas para el año seleccionado
        $currencyStats = Currency::with(['programs.applications' => function($q) use ($year) {
            $q->where('status', 'approved')->whereYear('created_at', $year);
        }])->get()->map(function($currency) {
            $revenue = 0;
            $participants = 0;
            
            foreach ($currency->programs as $program) {
                $programParticipants = $program->applications->count();
                $participants += $programParticipants;
                $revenue += $programParticipants * $program->cost;
            }
            
            return [
                'currency' => $currency,
                'revenue_original' => $revenue,
                'revenue_pyg' => $currency->convertToPyg($revenue),
                'participants' => $participants
            ];
        })->filter(function($item) {
            return $item['participants'] > 0;
        })->sortByDesc('revenue_pyg');

        // Totales del año
        $totalRevenuePyg = $monthlyData ? array_sum(array_column($monthlyData, 'total')) : 0;
        $totalParticipants = Application::where('status', 'approved')
            ->whereYear('created_at', $year)
            ->count();
            
        return view('admin.finance.report', compact(
            'year',
            'years',
            'monthlyData',
            'programRevenue',
            'currencyStats',
            'totalRevenuePyg',
            'totalParticipants'
        ));
    }
    
    /**
     * Exporta el reporte financiero
     */
    public function exportReport(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        
        // Implementar la exportación a Excel o CSV
        // ...
        
        return redirect()->back()->with('success', 'Reporte exportado correctamente.');
    }

    /**
     * Muestra todas las transacciones financieras
     */
    public function transactions(Request $request)
    {
        $query = FinancialTransaction::with(['currency', 'application', 'program', 'user', 'createdBy']);

        // Filtros
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $incomeCategories = FinancialTransaction::getIncomeCategories();
        $expenseCategories = FinancialTransaction::getExpenseCategories();

        return view('admin.finance.transactions', compact(
            'transactions', 
            'incomeCategories', 
            'expenseCategories'
        ));
    }

    /**
     * Muestra el formulario para crear una nueva transacción
     */
    public function createTransaction()
    {
        $currencies = Currency::orderBy('name')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $applications = Application::with(['user', 'program'])->orderBy('created_at', 'desc')->get();

        $incomeCategories = FinancialTransaction::getIncomeCategories();
        $expenseCategories = FinancialTransaction::getExpenseCategories();
        $paymentMethods = FinancialTransaction::getPaymentMethods();

        return view('admin.finance.create_transaction', compact(
            'currencies',
            'users',
            'programs',
            'applications',
            'incomeCategories',
            'expenseCategories',
            'paymentMethods'
        ));
    }

    /**
     * Almacena una nueva transacción financiera
     */
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'transaction_date' => 'required|date',
            'payment_method' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'application_id' => 'nullable|exists:applications,id',
            'program_id' => 'nullable|exists:programs,id',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $transaction = new FinancialTransaction();
        $transaction->type = $request->type;
        $transaction->category = $request->category;
        $transaction->description = $request->description;
        $transaction->amount = $request->amount;
        $transaction->currency_id = $request->currency_id;
        $transaction->transaction_date = $request->transaction_date;
        $transaction->payment_method = $request->payment_method;
        $transaction->reference = $request->reference;
        $transaction->application_id = $request->application_id;
        $transaction->program_id = $request->program_id;
        $transaction->user_id = $request->user_id;
        $transaction->created_by = auth()->id();
        $transaction->notes = $request->notes;
        $transaction->status = 'confirmed';

        // Manejar archivo de comprobante
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('receipts', $filename, 'public');
            $transaction->receipt_file = $path;
        }

        $transaction->save();

        // Convertir a guaraníes
        $transaction->convertToPyg();

        return redirect()->route('admin.finance.transactions')
            ->with('success', 'Transacción registrada correctamente.');
    }

    /**
     * Muestra una transacción específica
     */
    public function showTransaction($id)
    {
        $transaction = FinancialTransaction::with([
            'currency', 'application.user', 'program', 'user', 'createdBy'
        ])->findOrFail($id);

        return view('admin.finance.show_transaction', compact('transaction'));
    }

    /**
     * Muestra el formulario para editar una transacción
     */
    public function editTransaction($id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $currencies = Currency::orderBy('name')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $applications = Application::with(['user', 'program'])->orderBy('created_at', 'desc')->get();

        $incomeCategories = FinancialTransaction::getIncomeCategories();
        $expenseCategories = FinancialTransaction::getExpenseCategories();
        $paymentMethods = FinancialTransaction::getPaymentMethods();

        return view('admin.finance.edit_transaction', compact(
            'transaction',
            'currencies',
            'users',
            'programs',
            'applications',
            'incomeCategories',
            'expenseCategories',
            'paymentMethods'
        ));
    }

    /**
     * Actualiza una transacción financiera
     */
    public function updateTransaction(Request $request, $id)
    {
        $transaction = FinancialTransaction::findOrFail($id);

        $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'transaction_date' => 'required|date',
            'payment_method' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'application_id' => 'nullable|exists:applications,id',
            'program_id' => 'nullable|exists:programs,id',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $transaction->type = $request->type;
        $transaction->category = $request->category;
        $transaction->description = $request->description;
        $transaction->amount = $request->amount;
        $transaction->currency_id = $request->currency_id;
        $transaction->transaction_date = $request->transaction_date;
        $transaction->payment_method = $request->payment_method;
        $transaction->reference = $request->reference;
        $transaction->application_id = $request->application_id;
        $transaction->program_id = $request->program_id;
        $transaction->user_id = $request->user_id;
        $transaction->notes = $request->notes;

        // Manejar archivo de comprobante
        if ($request->hasFile('receipt_file')) {
            // Eliminar archivo anterior si existe
            if ($transaction->receipt_file && file_exists(storage_path('app/public/' . $transaction->receipt_file))) {
                unlink(storage_path('app/public/' . $transaction->receipt_file));
            }

            $file = $request->file('receipt_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('receipts', $filename, 'public');
            $transaction->receipt_file = $path;
        }

        $transaction->save();

        // Reconvertir a guaraníes
        $transaction->convertToPyg();

        return redirect()->route('admin.finance.transactions')
            ->with('success', 'Transacción actualizada correctamente.');
    }

    /**
     * Elimina una transacción financiera
     */
    public function destroyTransaction($id)
    {
        $transaction = FinancialTransaction::findOrFail($id);

        // Eliminar archivo de comprobante si existe
        if ($transaction->receipt_file && file_exists(storage_path('app/public/' . $transaction->receipt_file))) {
            unlink(storage_path('app/public/' . $transaction->receipt_file));
        }

        $transaction->delete();

        return redirect()->route('admin.finance.transactions')
            ->with('success', 'Transacción eliminada correctamente.');
    }
}
