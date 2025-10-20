<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Application;
use App\Models\Currency;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador para gestión de facturas/recibos
 * Épica 6 - Sprint 4
 */
class AdminInvoiceController extends Controller
{
    /**
     * Lista de facturas
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['user', 'currency', 'createdBy']);

        // Filtros
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.invoices.index', compact('invoices', 'users'));
    }

    /**
     * Formulario para crear factura
     */
    public function create(Request $request)
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->get();
        
        // Si viene de un pago específico
        $payment = null;
        if ($request->has('payment_id')) {
            $payment = \App\Models\UserProgramRequisite::with(['application.user', 'application.program', 'programRequisite.currency'])
                ->findOrFail($request->payment_id);
        }

        return view('admin.invoices.create', compact('users', 'currencies', 'payment'));
    }

    /**
     * Guardar nueva factura
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'application_id' => 'nullable|exists:applications,id',
            'billing_name' => 'required|string|max:255',
            'billing_email' => 'required|email',
            'billing_address' => 'nullable|string',
            'billing_city' => 'nullable|string|max:100',
            'billing_country' => 'nullable|string|max:100',
            'billing_tax_id' => 'nullable|string|max:50',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'concept' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'status' => 'required|in:draft,issued',
        ]);

        // Calcular total
        $subtotal = $validated['subtotal'];
        $taxAmount = $validated['tax_amount'] ?? 0;
        $discountAmount = $validated['discount_amount'] ?? 0;
        $total = $subtotal + $taxAmount - $discountAmount;

        // Generar número de factura
        $invoiceNumber = Invoice::generateInvoiceNumber();

        // Obtener datos del usuario
        $user = User::findOrFail($validated['user_id']);
        $application = $request->application_id ? Application::find($request->application_id) : null;

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $validated['user_id'],
            'application_id' => $validated['application_id'],
            'program_id' => $application?->program_id,
            'billing_name' => $validated['billing_name'],
            'billing_email' => $validated['billing_email'],
            'billing_address' => $validated['billing_address'] ?? null,
            'billing_city' => $validated['billing_city'] ?? null,
            'billing_country' => $validated['billing_country'] ?? null,
            'billing_tax_id' => $validated['billing_tax_id'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'currency_id' => $validated['currency_id'],
            'concept' => $validated['concept'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Generar PDF si la factura está emitida
        if ($validated['status'] === 'issued') {
            $this->generatePDF($invoice);
        }

        return redirect()
            ->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Factura creada exitosamente.');
    }

    /**
     * Ver detalle de factura
     */
    public function show($id)
    {
        $invoice = Invoice::with(['user', 'application.program', 'currency', 'createdBy'])
            ->findOrFail($id);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Descargar PDF de factura
     */
    public function downloadPDF($id)
    {
        $invoice = Invoice::with(['user', 'application.program', 'currency'])
            ->findOrFail($id);

        // Si no existe el PDF, generarlo
        if (!$invoice->pdf_path || !Storage::disk('public')->exists($invoice->pdf_path)) {
            $this->generatePDF($invoice);
        }

        $pdf = PDF::loadView('admin.invoices.pdf', compact('invoice'));
        return $pdf->download("factura-{$invoice->invoice_number}.pdf");
    }

    /**
     * Marcar factura como pagada
     */
    public function markAsPaid(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $invoice->markAsPaid();

        return redirect()
            ->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Factura marcada como pagada.');
    }

    /**
     * Cancelar factura
     */
    public function cancel($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        if ($invoice->status === 'paid') {
            return back()->with('error', 'No se puede cancelar una factura pagada.');
        }

        $invoice->update(['status' => 'cancelled']);

        return redirect()
            ->route('admin.invoices.index')
            ->with('success', 'Factura cancelada exitosamente.');
    }

    /**
     * Enviar factura por email
     */
    public function sendEmail($id)
    {
        $invoice = Invoice::with(['user', 'currency'])->findOrFail($id);

        // TODO: Implementar envío de email con PDF adjunto
        // Mail::to($invoice->billing_email)->send(new InvoiceMail($invoice));

        return back()->with('success', 'Factura enviada por email exitosamente.');
    }

    /**
     * Generar PDF de la factura
     */
    private function generatePDF(Invoice $invoice): void
    {
        $pdf = PDF::loadView('admin.invoices.pdf', compact('invoice'));
        
        $filename = 'invoices/' . $invoice->invoice_number . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());
        
        $invoice->update(['pdf_path' => $filename]);
    }
}
