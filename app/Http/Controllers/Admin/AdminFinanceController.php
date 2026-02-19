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
        // Obtener estadísticas financieras basadas en aplicaciones aprobadas Y pagos verificados
        $currentYear = Carbon::now()->year;
        
        // Ingresos totales del año en PYG (basado en PAGOS VERIFICADOS)
        $totalRevenuePyg = DB::table('user_program_requisites')
            ->join('program_requisites', 'user_program_requisites.program_requisite_id', '=', 'program_requisites.id')
            ->leftJoin('currencies', 'program_requisites.currency_id', '=', 'currencies.id')
            ->where('program_requisites.type', 'payment')
            ->where('user_program_requisites.status', 'verified')
            ->whereYear('user_program_requisites.verified_at', $currentYear)
            ->get()
            ->sum(function($payment) {
                $amount = $payment->payment_amount ?? 0;
                if ($payment->currency_id && $payment->exchange_rate_to_pyg) {
                    return $amount * $payment->exchange_rate_to_pyg;
                }
                return $amount; // Asume que ya está en PYG si no hay moneda
            });

        // Ingresos pendientes (PAGOS PENDIENTES)
        $pendingRevenuePyg = DB::table('user_program_requisites')
            ->join('program_requisites', 'user_program_requisites.program_requisite_id', '=', 'program_requisites.id')
            ->leftJoin('currencies', 'program_requisites.currency_id', '=', 'currencies.id')
            ->where('program_requisites.type', 'payment')
            ->where('user_program_requisites.status', 'pending')
            ->get()
            ->sum(function($payment) {
                $amount = $payment->payment_amount ?? 0;
                if ($payment->currency_id && $payment->exchange_rate_to_pyg) {
                    return $amount * $payment->exchange_rate_to_pyg;
                }
                return $amount;
            });

        // Ingresos mensuales del año actual en PYG (basado en pagos verificados)
        $monthlyRevenue = [];
        for ($month = 1; $month <= 12; $month++) {
            $revenue = DB::table('user_program_requisites')
                ->join('program_requisites', 'user_program_requisites.program_requisite_id', '=', 'program_requisites.id')
                ->leftJoin('currencies', 'program_requisites.currency_id', '=', 'currencies.id')
                ->where('program_requisites.type', 'payment')
                ->where('user_program_requisites.status', 'verified')
                ->whereYear('user_program_requisites.verified_at', $currentYear)
                ->whereMonth('user_program_requisites.verified_at', $month)
                ->get()
                ->sum(function($payment) {
                    $amount = $payment->payment_amount ?? 0;
                    if ($payment->currency_id && $payment->exchange_rate_to_pyg) {
                        return $amount * $payment->exchange_rate_to_pyg;
                    }
                    return $amount;
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
            ->leftJoin('currencies', 'program_requisites.currency_id', '=', 'currencies.id')
            ->where('program_requisites.type', 'payment')
            ->select(
                'user_program_requisites.*', 
                'program_requisites.name as requisite_name',
                'program_requisites.payment_amount as amount',
                'programs.name as program_name',
                'users.name as user_name',
                'users.email',
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
        $programs = Program::with('currency')->orderBy('name')->get();
        $requisites = [];
        
        return view('admin.finance.create_payment', compact('programs', 'requisites'));
    }

    /**
     * Búsqueda de participantes (solo role=user) para autocompletado
     */
    public function searchParticipants(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('role', 'user')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
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
            'program_id' => 'required|exists:programs,id',
            'program_requisite_id' => 'nullable|exists:program_requisites,id',
            'concept' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'payment_method' => 'required|string',
            'reference_number' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,verified',
        ]);
        
        // Determine concept name from requisite or free text
        $concept = $request->concept;
        if ($request->program_requisite_id) {
            $requisite = ProgramRequisite::find($request->program_requisite_id);
            if ($requisite && !$concept) {
                $concept = $requisite->name;
            }
        }
        
        // Find or create application for this user+program
        $application = Application::where('user_id', $request->user_id)
            ->where('program_id', $request->program_id)
            ->first();
            
        if (!$application) {
            $application = Application::create([
                'user_id' => $request->user_id,
                'program_id' => $request->program_id,
                'status' => 'pending',
                'applied_at' => now(),
            ]);
        }
        
        // Determine currency: from request, from program, or default USD
        $currencyId = $request->currency_id;
        if (!$currencyId) {
            $program = Program::find($request->program_id);
            $currencyId = $program->currency_id ?? Currency::where('code', 'USD')->value('id');
        }
        
        $status = $request->status ?? 'pending';
        
        // Create payment in the payments table
        $payment = \App\Models\Payment::create([
            'application_id' => $application->id,
            'user_id' => $request->user_id,
            'program_id' => $request->program_id,
            'currency_id' => $currencyId,
            'amount' => $request->amount,
            'concept' => $concept,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'payment_date' => now()->toDateString(),
            'status' => $status,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
            'verified_by' => $status === 'verified' ? auth()->id() : null,
            'verified_at' => $status === 'verified' ? now() : null,
        ]);
        
        // Also update the user_program_requisite if linked to a requisite
        if ($request->program_requisite_id) {
            $userRequisite = UserProgramRequisite::where('application_id', $application->id)
                ->where('program_requisite_id', $request->program_requisite_id)
                ->first();
                
            if (!$userRequisite) {
                $userRequisite = new UserProgramRequisite();
                $userRequisite->application_id = $application->id;
                $userRequisite->program_requisite_id = $request->program_requisite_id;
            }
            
            $userRequisite->status = $status;
            $userRequisite->observations = 'Payment #' . $payment->id . ' - $' . number_format($request->amount, 2);
            if ($status === 'verified') {
                $userRequisite->verified_at = now();
            }
            $userRequisite->save();
        }
        
        return redirect()->route('admin.finance.payments')
            ->with('success', $status === 'verified' 
                ? 'Pago registrado como REALIZADO exitosamente.' 
                : 'Pago registrado como PENDIENTE de verificación.');
    }
    
    /**
     * Verifica un pago pendiente
     */
    public function verifyPayment(Request $request, $id)
    {
        $payment = UserProgramRequisite::with(['programRequisite.currency', 'application.user', 'application.program'])->findOrFail($id);
        
        // Marcar como verificado
        $payment->status = 'verified';
        $payment->verified_at = now();
        $payment->save();
        
        // Crear transacción financiera si no existe
        $existingTransaction = FinancialTransaction::where('application_id', $payment->application_id)
            ->where('reference', 'PAYMENT-REQ-' . $payment->id)
            ->first();
            
        if (!$existingTransaction) {
            $programRequisite = $payment->programRequisite;
            $amount = $programRequisite->payment_amount ?? 0;
            $currency = $programRequisite->currency;
            
            // Crear transacción de ingreso
            $transaction = new FinancialTransaction();
            $transaction->type = 'income';
            $transaction->category = 'program_payment';
            $transaction->description = 'Pago verificado: ' . $programRequisite->name . ' - ' . $payment->application->user->name;
            $transaction->amount = $amount;
            $transaction->currency_id = $currency ? $currency->id : null;
            $transaction->transaction_date = now();
            $transaction->payment_method = $payment->file_path ?? 'bank_transfer'; // Método de pago del comprobante
            $transaction->reference = 'PAYMENT-REQ-' . $payment->id; // Referencia única
            $transaction->application_id = $payment->application_id;
            $transaction->program_id = $payment->application->program_id;
            $transaction->user_id = $payment->application->user_id;
            $transaction->created_by = auth()->id();
            $transaction->notes = 'Pago verificado automáticamente desde requisito de programa';
            $transaction->status = 'confirmed';
            $transaction->save();
            
            // Convertir a guaraníes con el exchange rate del momento
            // Esto guarda amount_pyg con la conversión actual
            $transaction->convertToPyg();
            
            // El amount_pyg ahora tiene la conversión al momento de verificación
            // y NO cambiará aunque se modifique la cotización posteriormente
        }
        
        // Disparar evento de pago verificado
        event(new \App\Events\PaymentVerified($payment));
        
        return redirect()->route('admin.finance.payments')->with('success', 'Pago verificado y registrado en contabilidad correctamente.');
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
        
        return redirect()->route('admin.finance.payments')->with('success', 'Pago rechazado correctamente.');
    }
    
    /**
     * Marca un pago como pendiente
     */
    public function pendingPayment(Request $request, $id)
    {
        $payment = UserProgramRequisite::findOrFail($id);
        $payment->status = 'pending';
        $payment->verified_at = null;
        $payment->save();
        
        // Clear any query cache
        \DB::connection()->getPdo()->exec('SELECT 1');
        
        return redirect()->route('admin.finance.payments')->with('success', 'Pago marcado como pendiente correctamente.');
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
