<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'user_id' => 'required|exists:users,id',
            'program_id' => 'nullable|exists:programs,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0',
            'concept' => 'required|string',
            'other_concept' => 'nullable|string|required_if:concept,Otro',
            'status' => 'required|in:pending,verified',
            'created_by' => 'nullable|exists:users,id',
        ]);

        // Si el concepto es "Otro" y se proporcionó un concepto personalizado, usarlo
        if ($validated['concept'] === 'Otro' && !empty($validated['other_concept'])) {
            $validated['concept'] = $validated['other_concept'];
        }
        
        // Remover other_concept del array ya que no existe en la tabla
        unset($validated['other_concept']);

        // Establecer valores por defecto
        $validated['payment_date'] = now()->toDateString(); // Fecha actual
        $validated['created_by'] = $validated['created_by'] ?? auth()->id();
        
        // Si el pago es "Realizado" (verified), establecer verificación automática
        if ($validated['status'] === 'verified') {
            $validated['verified_by'] = auth()->id();
            $validated['verified_at'] = now();
        }

        Payment::create($validated);

        $message = $validated['status'] === 'verified' 
            ? 'Pago registrado como REALIZADO exitosamente.' 
            : 'Pago registrado como PENDIENTE de verificación.';

        return redirect()
            ->route('admin.participants.show', $validated['application_id'])
            ->with('success', $message);
    }

    /**
     * Verify a payment.
     */
    public function verify($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->verify(auth()->id());

        return redirect()
            ->route('admin.participants.show', $payment->application_id)
            ->with('success', 'Pago verificado exitosamente.');
    }

    /**
     * Reject a payment.
     */
    public function reject(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        $notes = $request->input('notes');
        $payment->reject(auth()->id(), $notes);

        return redirect()
            ->route('admin.participants.show', $payment->application_id)
            ->with('warning', 'Pago rechazado.');
    }

    /**
     * Update a payment.
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'concept' => 'required|string',
            'reference_number' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'receipt_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Subir nuevo comprobante si existe
        if ($request->hasFile('receipt_path')) {
            // Eliminar comprobante anterior
            if ($payment->receipt_path) {
                Storage::disk('public')->delete($payment->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt_path')->store('payments/receipts', 'public');
        }

        $payment->update($validated);

        return redirect()
            ->route('admin.participants.show', $payment->application_id)
            ->with('success', 'Pago actualizado exitosamente.');
    }

    /**
     * Delete a payment.
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $applicationId = $payment->application_id;

        // Eliminar comprobante si existe
        if ($payment->receipt_path) {
            Storage::disk('public')->delete($payment->receipt_path);
        }

        $payment->delete();

        return redirect()
            ->route('admin.participants.show', $applicationId)
            ->with('success', 'Pago eliminado exitosamente.');
    }
}
