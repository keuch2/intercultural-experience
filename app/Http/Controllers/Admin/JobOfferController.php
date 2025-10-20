<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use App\Models\Sponsor;
use App\Models\HostCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobOfferController extends Controller
{
    /**
     * Display a listing of job offers
     */
    public function index(Request $request)
    {
        $query = JobOffer::with(['sponsor', 'hostCompany']);

        // Filtros
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('position', 'like', "%{$search}%")
                  ->orWhere('job_offer_id', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('sponsor_id')) {
            $query->where('sponsor_id', $request->sponsor_id);
        }

        if ($request->has('host_company_id')) {
            $query->where('host_company_id', $request->host_company_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('state')) {
            $query->byState($request->state);
        }

        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        $offers = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Para los filtros
        $sponsors = Sponsor::where('is_active', true)->get();
        $companies = HostCompany::where('is_active', true)->get();

        return view('admin.job-offers.index', compact('offers', 'sponsors', 'companies'));
    }

    /**
     * Show the form for creating a new job offer
     */
    public function create()
    {
        $sponsors = Sponsor::where('is_active', true)->get();
        $companies = HostCompany::where('is_active', true)->get();
        
        return view('admin.job-offers.create', compact('sponsors', 'companies'));
    }

    /**
     * Store a newly created job offer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_offer_id' => 'required|string|max:50|unique:job_offers,job_offer_id',
            'sponsor_id' => 'required|exists:sponsors,id',
            'host_company_id' => 'required|exists:host_companies,id',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|min:0|gte:salary_min',
            'hours_per_week' => 'required|integer|min:1|max:60',
            'housing_type' => 'required|in:provided,assisted,not_provided',
            'housing_cost' => 'nullable|numeric|min:0',
            'total_slots' => 'required|integer|min:1',
            'available_slots' => 'required|integer|min:0|lte:total_slots',
            'required_english_level' => 'required|in:A2,B1,B1+,B2,C1,C2',
            'required_gender' => 'required|in:male,female,any',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:available,full,cancelled',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $offer = JobOffer::create($request->all());

        return redirect()->route('admin.job-offers.index')
            ->with('success', 'Oferta laboral creada exitosamente');
    }

    /**
     * Display the specified job offer
     */
    public function show($id)
    {
        $offer = JobOffer::with(['sponsor', 'hostCompany', 'reservations.user'])
            ->findOrFail($id);

        return view('admin.job-offers.show', compact('offer'));
    }

    /**
     * Show the form for editing the specified job offer
     */
    public function edit($id)
    {
        $offer = JobOffer::findOrFail($id);
        $sponsors = Sponsor::where('is_active', true)->get();
        $companies = HostCompany::where('is_active', true)->get();
        
        return view('admin.job-offers.edit', compact('offer', 'sponsors', 'companies'));
    }

    /**
     * Update the specified job offer
     */
    public function update(Request $request, $id)
    {
        $offer = JobOffer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'job_offer_id' => 'required|string|max:50|unique:job_offers,job_offer_id,' . $id,
            'sponsor_id' => 'required|exists:sponsors,id',
            'host_company_id' => 'required|exists:host_companies,id',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|min:0|gte:salary_min',
            'hours_per_week' => 'required|integer|min:1|max:60',
            'housing_type' => 'required|in:provided,assisted,not_provided',
            'housing_cost' => 'nullable|numeric|min:0',
            'total_slots' => 'required|integer|min:1',
            'available_slots' => 'required|integer|min:0|lte:total_slots',
            'required_english_level' => 'required|in:A2,B1,B1+,B2,C1,C2',
            'required_gender' => 'required|in:male,female,any',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:available,full,cancelled',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $offer->update($request->all());

        return redirect()->route('admin.job-offers.index')
            ->with('success', 'Oferta laboral actualizada exitosamente');
    }

    /**
     * Remove the specified job offer
     */
    public function destroy($id)
    {
        $offer = JobOffer::findOrFail($id);

        // Verificar si tiene reservas activas
        if ($offer->reservations()->whereIn('status', ['pending', 'confirmed'])->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar la oferta porque tiene reservas activas');
        }

        $offer->delete();

        return redirect()->route('admin.job-offers.index')
            ->with('success', 'Oferta laboral eliminada exitosamente');
    }

    /**
     * Toggle job offer status
     */
    public function toggleStatus($id)
    {
        $offer = JobOffer::findOrFail($id);
        
        if ($offer->status === 'available') {
            $offer->update(['status' => 'cancelled']);
            $message = 'Oferta laboral cancelada';
        } else {
            $offer->update(['status' => 'available']);
            $message = 'Oferta laboral activada';
        }

        return redirect()->back()->with('success', $message);
    }
}
