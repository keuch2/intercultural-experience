<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
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
     * Update exchange rates
     */
    public function updateRates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currencies' => 'required|array',
            'currencies.*' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        foreach ($request->currencies as $currencyId => $rate) {
            Currency::where('id', $currencyId)->update([
                'exchange_rate_to_pyg' => $rate
            ]);
        }

        return back()->with('success', 'Cotizaciones actualizadas exitosamente.');
    }
}
