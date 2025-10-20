<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminCurrencyController extends Controller
{
    /**
     * Display a listing of currencies.
     */
    public function index()
    {
        $currencies = Currency::withCount('programs')->orderBy('code')->get();
        return view('admin.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create()
    {
        return view('admin.currencies.create');
    }

    /**
     * Store a newly created currency.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:3|unique:currencies,code',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate_to_pyg' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Currency::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'symbol' => $request->symbol,
            'exchange_rate_to_pyg' => $request->exchange_rate_to_pyg,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.currencies.index')
                        ->with('success', 'Moneda creada exitosamente.');
    }

    /**
     * Display the specified currency.
     */
    public function show(Currency $currency)
    {
        $programs = $currency->programs()->with('applications')->get();
        return view('admin.currencies.show', compact('currency', 'programs'));
    }

    /**
     * Show the form for editing the specified currency.
     */
    public function edit(Currency $currency)
    {
        return view('admin.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency.
     */
    public function update(Request $request, Currency $currency)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:3|unique:currencies,code,' . $currency->id,
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate_to_pyg' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $currency->update([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'symbol' => $request->symbol,
            'exchange_rate_to_pyg' => $request->exchange_rate_to_pyg,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.currencies.index')
                        ->with('success', 'Moneda actualizada exitosamente.');
    }

    /**
     * Remove the specified currency.
     */
    public function destroy(Currency $currency)
    {
        // Check if currency is being used by any programs
        if ($currency->programs()->count() > 0) {
            return back()->with('error', 'No se puede eliminar esta moneda porque está siendo utilizada por programas.');
        }

        $currency->delete();

        return redirect()->route('admin.currencies.index')
                        ->with('success', 'Moneda eliminada exitosamente.');
    }

    /**
     * Actualiza las tasas de cambio
     */
    public function updateRates(Request $request)
    {
        $request->validate([
            'rates' => 'required|array',
            'rates.*' => 'required|numeric|min:0',
        ]);

        $updatedCurrencies = 0;

        foreach ($request->rates as $currencyId => $rate) {
            $currency = Currency::findOrFail($currencyId);
            $oldRate = $currency->exchange_rate_to_pyg;
            $currency->exchange_rate_to_pyg = $rate;
            $currency->updated_at = now();
            $currency->save();
            
            // Log para auditoría
            \Log::info("Currency rate updated", [
                'currency_id' => $currencyId,
                'currency_code' => $currency->code,
                'old_rate' => $oldRate,
                'new_rate' => $rate,
                'updated_by' => auth()->id(),
                'timestamp' => now()
            ]);
            
            $updatedCurrencies++;
        }

        return redirect()->back()->with('success', "Tasas de cambio actualizadas exitosamente ({$updatedCurrencies} monedas).");
    }

    /**
     * API para obtener tasas de conversión en tiempo real
     */
    public function getRates()
    {
        $currencies = Currency::active()->get();
        
        $rates = $currencies->mapWithKeys(function ($currency) {
            return [
                $currency->code => [
                    'id' => $currency->id,
                    'name' => $currency->name,
                    'symbol' => $currency->symbol,
                    'rate_to_pyg' => $currency->exchange_rate_to_pyg,
                    'updated_at' => $currency->updated_at->toISOString()
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'base_currency' => 'PYG',
            'rates' => $rates,
            'last_updated' => now()->toISOString()
        ]);
    }

    /**
     * Convertir montos entre monedas
     */
    public function convert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency' => 'required|exists:currencies,code',
            'to_currency' => 'required|exists:currencies,code',
        ]);

        $fromCurrency = Currency::where('code', $request->from_currency)->first();
        $toCurrency = Currency::where('code', $request->to_currency)->first();

        // Convertir a PYG primero, luego a la moneda destino
        $amountInPyg = $fromCurrency->convertToPyg($request->amount);
        $convertedAmount = $toCurrency->convertFromPyg($amountInPyg);

        return response()->json([
            'success' => true,
            'original_amount' => $request->amount,
            'from_currency' => $fromCurrency->code,
            'to_currency' => $toCurrency->code,
            'converted_amount' => round($convertedAmount, 2),
            'rate_used' => round($amountInPyg / $request->amount, 4),
            'calculation_date' => now()->toISOString()
        ]);
    }
}
