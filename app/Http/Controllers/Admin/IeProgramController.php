<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Currency;
use App\Models\Institution;
use Illuminate\Support\Facades\Storage;

class IeProgramController extends Controller
{
    /**
     * Display a listing of IE programs.
     */
    public function index(Request $request)
    {
        $query = Program::ie()->with(['currency', 'applications']);
        
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
        $countries = Program::ie()->select('country')->distinct()->pluck('country');
        $subcategories = Program::ie()->select('subcategory')->distinct()->whereNotNull('subcategory')->pluck('subcategory');
        
        return view('admin.ie-programs.index', compact('programs', 'countries', 'subcategories'));
    }

    /**
     * Show the form for creating a new IE program.
     */
    public function create()
    {
        $currencies = Currency::active()->get();
        $institutions = Institution::active()->get();
        $subcategories = $this->getIeSubcategories();
        
        return view('admin.ie-programs.create', compact('currencies', 'institutions', 'subcategories'));
    }

    /**
     * Store a newly created IE program in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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
        
        $data = $request->all();
        $data['main_category'] = 'IE';
        
        // Asignar valores por defecto si son nulos
        $data['capacity'] = $data['capacity'] ?? 0;
        $data['available_slots'] = $data['available_slots'] ?? 0;
        
        // Manejar subida de imagen
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $originalName = $image->getClientOriginalName();
            
            // Sanitizar nombre: eliminar espacios y caracteres especiales
            $sanitizedName = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $originalName);
            $sanitizedName = preg_replace('/_+/', '_', $sanitizedName);
            $imageName = time() . '_' . $sanitizedName;
            
            $path = Storage::disk('public')->putFileAs('programs', $image, $imageName);
            if (!$path) {
                \Log::error('Error al guardar imagen en store()', ['nombre' => $imageName]);
                return redirect()->back()->withErrors(['image' => 'Error al subir la imagen']);
            }
            $data['image'] = 'programs/' . $imageName;
        }
        
        Program::create($data);
        
        return redirect()->route('admin.ie-programs.index')
            ->with('success', 'Programa IE creado correctamente.');
    }

    /**
     * Display the specified IE program.
     */
    public function show(Program $program)
    {
        // Verificar que el programa es IE
        if ($program->main_category !== 'IE') {
            abort(404);
        }
        
        $program->load(['currency', 'applications', 'institution']);
        
        return view('admin.ie-programs.show', compact('program'));
    }

    /**
     * Show the form for editing the specified IE program.
     */
    public function edit(Program $program)
    {
        // Verificar que el programa es IE
        if ($program->main_category !== 'IE') {
            abort(404);
        }
        
        $currencies = Currency::active()->get();
        $institutions = Institution::active()->get();
        $subcategories = $this->getIeSubcategories();
        $requisites = $program->requisites()->orderBy('order')->get();
        
        return view('admin.ie-programs.edit', compact('program', 'currencies', 'institutions', 'subcategories', 'requisites'));
    }

    /**
     * Update the specified IE program in storage.
     */
    public function update(Request $request, Program $program)
    {
        // Verificar que el programa es IE
        if ($program->main_category !== 'IE') {
            abort(404);
        }
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'country' => 'required|string|max:255',
            'subcategory' => 'required|string|in:' . implode(',', $this->getIeSubcategories()),
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
                $sanitizedName = preg_replace('/_+/', '_', $sanitizedName); // Eliminar guiones bajos múltiples
                $imageName = time() . '_' . $sanitizedName;
                
                // Guardar la nueva imagen
                \Log::info('Intentando guardar imagen', [
                    'programa_id' => $program->id,
                    'nombre_original' => $originalName,
                    'nombre_sanitizado' => $imageName,
                    'directorio' => 'public/programs',
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize()
                ]);
                
                // Usar Storage::disk('public') para guardar en el disco público
                $path = Storage::disk('public')->putFileAs('programs', $image, $imageName);
                
                \Log::info('Resultado de putFileAs', [
                    'path' => $path,
                    'exists_after_store' => Storage::exists($path),
                    'disk_path' => Storage::path($path)
                ]);
                
                if (!$path) {
                    throw new \Exception('No se pudo guardar la imagen');
                }
                
                // Verificar que el archivo realmente existe
                $fullPath = storage_path('app/public/' . $path);
                if (!file_exists($fullPath)) {
                    \Log::error('Archivo no existe después de putFileAs', [
                        'path' => $path,
                        'full_path' => $fullPath,
                        'storage_disk_exists' => Storage::disk('public')->exists($path)
                    ]);
                    throw new \Exception('El archivo no se guardó correctamente en el disco');
                }
                
                // Eliminar imagen anterior solo después de guardar la nueva exitosamente
                if ($program->image && Storage::disk('public')->exists($program->image)) {
                    Storage::disk('public')->delete($program->image);
                }
                
                $validatedData['image'] = 'programs/' . $imageName;
                
                \Log::info('Imagen guardada exitosamente', [
                    'programa_id' => $program->id,
                    'imagen' => $imageName,
                    'path' => $path,
                    'full_path' => $fullPath,
                    'file_size' => filesize($fullPath)
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
        
        // Agregar datos específicos para IE
        $validatedData['main_category'] = 'IE';
        $validatedData['is_active'] = $validatedData['is_active'] ?? true;
        
        // Asignar valores por defecto si son nulos
        $validatedData['capacity'] = $validatedData['capacity'] ?? 0;
        $validatedData['available_slots'] = $validatedData['available_slots'] ?? 0;
        
        $program->update($validatedData);
        
        return redirect()->route('admin.ie-programs.index')
            ->with('success', 'Programa IE actualizado correctamente.');
    }

    /**
     * Remove the specified IE program from storage.
     */
    public function destroy(Program $program)
    {
        // Verificar que el programa es IE
        if ($program->main_category !== 'IE') {
            abort(404);
        }
        
        $program->delete();
        
        return redirect()->route('admin.ie-programs.index')
            ->with('success', 'Programa IE eliminado correctamente.');
    }

    /**
     * Get available IE subcategories
     */
    private function getIeSubcategories()
    {
        return [
            'Work and Travel',
            'Au Pair',
            'Teacher\'s Program',
            'Internship',
            'Study Abroad',
            'Volunteer Program',
            'Language Exchange',
        ];
    }
}
