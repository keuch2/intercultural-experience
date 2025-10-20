<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HostCompanyController extends Controller
{
    /**
     * Display a listing of host companies
     */
    public function index(Request $request)
    {
        $query = HostCompany::query();

        // Filtros
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('state')) {
            $query->byState($request->state);
        }

        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        if ($request->has('industry')) {
            $query->byIndustry($request->industry);
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        $companies = $query->withCount('jobOffers')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.host-companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new host company
     */
    public function create()
    {
        return view('admin.host-companies.create');
    }

    /**
     * Store a newly created host company
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $company = HostCompany::create($request->all());

        return redirect()->route('admin.host-companies.index')
            ->with('success', 'Empresa host creada exitosamente');
    }

    /**
     * Display the specified host company
     */
    public function show($id)
    {
        $company = HostCompany::with('jobOffers.sponsor')
            ->withCount('jobOffers')
            ->findOrFail($id);

        return view('admin.host-companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified host company
     */
    public function edit($id)
    {
        $company = HostCompany::findOrFail($id);
        return view('admin.host-companies.edit', compact('company'));
    }

    /**
     * Update the specified host company
     */
    public function update(Request $request, $id)
    {
        $company = HostCompany::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $company->update($request->all());

        return redirect()->route('admin.host-companies.index')
            ->with('success', 'Empresa host actualizada exitosamente');
    }

    /**
     * Remove the specified host company
     */
    public function destroy($id)
    {
        $company = HostCompany::findOrFail($id);

        // Verificar si tiene job offers asociadas
        if ($company->jobOffers()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar la empresa porque tiene ofertas laborales asociadas');
        }

        $company->delete();

        return redirect()->route('admin.host-companies.index')
            ->with('success', 'Empresa host eliminada exitosamente');
    }

    /**
     * Toggle host company active status
     */
    public function toggleStatus($id)
    {
        $company = HostCompany::findOrFail($id);
        $company->update(['is_active' => !$company->is_active]);

        $status = $company->is_active ? 'activada' : 'desactivada';
        return redirect()->back()
            ->with('success', "Empresa {$status} exitosamente");
    }

    /**
     * Update company rating
     */
    public function updateRating(Request $request, $id)
    {
        $company = HostCompany::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $company->updateRating($request->rating);

        return redirect()->back()
            ->with('success', 'Rating actualizado exitosamente');
    }
}
