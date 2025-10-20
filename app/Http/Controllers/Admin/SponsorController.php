<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SponsorController extends Controller
{
    /**
     * Display a listing of sponsors
     */
    public function index(Request $request)
    {
        $query = Sponsor::query();

        // Filtros
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('country')) {
            $query->byCountry($request->country);
        }

        $sponsors = $query->withCount('jobOffers')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.sponsors.index', compact('sponsors'));
    }

    /**
     * Show the form for creating a new sponsor
     */
    public function create()
    {
        return view('admin.sponsors.create');
    }

    /**
     * Store a newly created sponsor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:sponsors,code',
            'country' => 'required|string|max:100',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'terms_and_conditions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $sponsor = Sponsor::create($request->all());

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor creado exitosamente');
    }

    /**
     * Display the specified sponsor
     */
    public function show($id)
    {
        $sponsor = Sponsor::with('jobOffers.hostCompany')
            ->withCount('jobOffers')
            ->findOrFail($id);

        return view('admin.sponsors.show', compact('sponsor'));
    }

    /**
     * Show the form for editing the specified sponsor
     */
    public function edit($id)
    {
        $sponsor = Sponsor::findOrFail($id);
        return view('admin.sponsors.edit', compact('sponsor'));
    }

    /**
     * Update the specified sponsor
     */
    public function update(Request $request, $id)
    {
        $sponsor = Sponsor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:sponsors,code,' . $id,
            'country' => 'required|string|max:100',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'terms_and_conditions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $sponsor->update($request->all());

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor actualizado exitosamente');
    }

    /**
     * Remove the specified sponsor
     */
    public function destroy($id)
    {
        $sponsor = Sponsor::findOrFail($id);

        // Verificar si tiene job offers asociadas
        if ($sponsor->jobOffers()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el sponsor porque tiene ofertas laborales asociadas');
        }

        $sponsor->delete();

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor eliminado exitosamente');
    }

    /**
     * Toggle sponsor active status
     */
    public function toggleStatus($id)
    {
        $sponsor = Sponsor::findOrFail($id);
        $sponsor->update(['is_active' => !$sponsor->is_active]);

        $status = $sponsor->is_active ? 'activado' : 'desactivado';
        return redirect()->back()
            ->with('success', "Sponsor {$status} exitosamente");
    }
}
