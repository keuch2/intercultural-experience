<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Currency;
use App\Models\Institution;
use Illuminate\Support\Facades\Storage;

class YfuProgramController extends Controller
{
    /**
     * Display a listing of YFU programs.
     */
    public function index(Request $request)
    {
        $query = Program::yfu()->with(['currency', 'applications']);
        
        // Aplicar filtros si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('country') && $request->input('country') != '') {
            $query->where('country', $request->input('country'));
        }
        
        if ($request->has('subcategory') && $request->input('subcategory') != '') {
            $query->where('subcategory', $request->input('subcategory'));
        }
        
        if ($request->has('is_active') && $request->input('is_active') != '') {
            $query->where('is_active', $request->input('is_active'));
        }
        
        $programs = $query->withCount('applications')->orderBy('created_at', 'desc')->paginate(15);
        
        // Obtener valores únicos para filtros
        $countries = Program::yfu()->select('country')->distinct()->pluck('country');
        $subcategories = Program::yfu()->select('subcategory')->distinct()->whereNotNull('subcategory')->pluck('subcategory');
        
        return view('admin.yfu-programs.index', compact('programs', 'countries', 'subcategories'));
    }

    /**
     * Show the form for creating a new YFU program.
     */
    public function create()
    {
        $currencies = Currency::active()->get();
        $institutions = Institution::active()->get();
        $subcategories = $this->getYfuSubcategories();
        
        return view('admin.yfu-programs.create', compact('currencies', 'institutions', 'subcategories'));
    }

    /**
     * Store a newly created YFU program in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'required|string|max:100',
            'subcategory' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'application_deadline' => 'nullable|date',
            'duration' => 'nullable|integer',
            'capacity' => 'nullable|integer',
            'cost' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'institution_id' => 'nullable|exists:institutions,id',
            'is_active' => 'required|in:0,1',
        ]);
        
        $data = $request->all();
        $data['main_category'] = 'YFU';
        
        // Asignar valores por defecto si son nulos
        $data['capacity'] = $data['capacity'] ?? 0;
        $data['available_slots'] = $data['available_slots'] ?? 0;
        
        Program::create($data);
        
        return redirect()->route('admin.yfu-programs.index')
            ->with('success', 'Programa YFU creado correctamente.');
    }

    /**
     * Display the specified YFU program.
     */
    public function show(Program $program)
    {
        // Verificar que el programa es YFU
        if ($program->main_category !== 'YFU') {
            abort(404);
        }
        
        $program->load(['currency', 'applications', 'institution']);
        
        return view('admin.yfu-programs.show', compact('program'));
    }

    /**
     * Show the form for editing the specified YFU program.
     */
    public function edit(Program $program)
    {
        // Verificar que el programa es YFU
        if ($program->main_category !== 'YFU') {
            abort(404);
        }
        
        $currencies = Currency::active()->get();
        $institutions = Institution::active()->get();
        $subcategories = $this->getYfuSubcategories();
        
        return view('admin.yfu-programs.edit', compact('program', 'currencies', 'institutions', 'subcategories'));
    }

    /**
     * Update the specified YFU program in storage.
     */
    public function update(Request $request, Program $program)
    {
        // Verificar que el programa es YFU
        if ($program->main_category !== 'YFU') {
            abort(404);
        }
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'required|string|max:100',
            'subcategory' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'application_deadline' => 'nullable|date',
            'duration' => 'nullable|integer',
            'capacity' => 'nullable|integer',
            'cost' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'institution_id' => 'nullable|exists:institutions,id',
            'is_active' => 'required|in:0,1',
        ]);
        
        // Manejar subida de imagen
        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $originalName = $image->getClientOriginalName();
                
                // Sanitizar nombre: eliminar espacios y caracteres especiales
                $sanitizedName = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $originalName);
                $sanitizedName = preg_replace('/_+/', '_', $sanitizedName);
                $imageName = time() . '_' . $sanitizedName;
                
                // Guardar la nueva imagen usando Storage::disk('public')
                $path = Storage::disk('public')->putFileAs('programs', $image, $imageName);
                
                if (!$path) {
                    throw new \Exception('No se pudo guardar la imagen');
                }
                
                // Eliminar imagen anterior solo después de guardar la nueva exitosamente
                if ($program->image && Storage::disk('public')->exists($program->image)) {
                    Storage::disk('public')->delete($program->image);
                }
                
                $validatedData['image'] = 'programs/' . $imageName;
                
                \Log::info('Imagen guardada exitosamente', [
                    'programa_id' => $program->id,
                    'imagen' => $imageName,
                    'path' => $path
                ]);
            } catch (\Exception $e) {
                \Log::error('Error al guardar imagen', [
                    'programa_id' => $program->id,
                    'error' => $e->getMessage()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['image' => 'Error al subir la imagen: ' . $e->getMessage()]);
            }
        } else {
            // Si no se sube nueva imagen, mantener la existente
            unset($validatedData['image']);
        }
        
        $validatedData['main_category'] = 'YFU';
        
        // Asignar valores por defecto si son nulos
        $validatedData['capacity'] = $validatedData['capacity'] ?? 0;
        $validatedData['available_slots'] = $validatedData['available_slots'] ?? 0;
        
        $program->update($validatedData);
        
        return redirect()->route('admin.yfu-programs.index')
            ->with('success', 'Programa YFU actualizado correctamente.');
    }

    /**
     * Remove the specified YFU program from storage.
     */
    public function destroy(Program $program)
    {
        // Verificar que el programa es YFU
        if ($program->main_category !== 'YFU') {
            abort(404);
        }
        
        $program->delete();
        
        return redirect()->route('admin.yfu-programs.index')
            ->with('success', 'Programa YFU eliminado correctamente.');
    }

    /**
     * Get available YFU subcategories
     */
    private function getYfuSubcategories()
    {
        return [
            'High School Exchange',
            'University Exchange',
            'Language Immersion',
            'Cultural Exchange',
            'Summer Programs',
            'Gap Year Programs',
            'Family Programs',
        ];
    }
}
