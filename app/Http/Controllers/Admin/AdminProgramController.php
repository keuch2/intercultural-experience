<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Currency;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use Illuminate\Support\Facades\Storage;

class AdminProgramController extends Controller
{
    /**
     * Muestra la lista de programas.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Program::with(['currency', 'applications']);
        
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
        
        if ($request->has('category') && $request->input('category') != '') {
            $query->where('category', $request->input('category'));
        }
        
        if ($request->has('currency') && $request->input('currency') != '') {
            $query->whereHas('currency', function($q) use ($request) {
                $q->where('code', $request->input('currency'));
            });
        }
        
        if ($request->has('is_active') && $request->input('is_active') != '') {
            $query->where('is_active', $request->input('is_active'));
        }
        
        $programs = $query->withCount('applications')->orderBy('created_at', 'desc')->paginate(15);
        
        // Obtener monedas para filtros
        $currencies = Currency::active()->orderBy('code')->get();
        
        // Estadísticas generales
        $stats = [
            'total' => Program::count(),
            'active' => Program::where('is_active', true)->count(),
            'applications' => \App\Models\Application::count(),
            'countries' => Program::distinct('country')->count('country'),
        ];
        
        return view('admin.programs.index', compact('programs', 'currencies', 'stats'));
    }

    /**
     * Muestra el formulario para crear un nuevo programa.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currencies = Currency::active()->orderBy('code')->get();
        return view('admin.programs.create', compact('currencies'));
    }

    /**
     * Almacena un nuevo programa en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProgramRequest $request)
    {
        $program = new Program();
        $program->name = $request->name;
        $program->description = $request->description;
        $program->country = $request->country;
        $program->category = $request->category;
        $program->location = $request->location;
        $program->start_date = $request->start_date;
        $program->end_date = $request->end_date;
        $program->application_deadline = $request->application_deadline;
        $program->duration = $request->duration;
        $program->credits = $request->credits;
        $program->capacity = $request->capacity;
        $program->available_spots = $request->capacity; // Inicialmente todos los cupos disponibles
        $program->cost = $request->cost;
        $program->currency_id = $request->currency_id;
        $program->image_url = $request->image_url;
        $program->is_active = $request->is_active;
        
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('programs', 'public');
            $program->image_url = Storage::url($imagePath);
        }
        
        $program->save();
        
        return redirect()->route('admin.programs.index')
            ->with('success', 'Programa creado correctamente.');
    }

    /**
     * Muestra la información de un programa específico.
     *
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function show(Program $program)
    {
        // Cargar aplicaciones relacionadas con este programa
        $applications = $program->applications()->with('user')->paginate(10);
        
        // Estadísticas de aplicaciones
        $applicationStats = [
            'total' => $program->applications()->count(),
            'pending' => $program->applications()->where('status', 'pending')->count(),
            'in_review' => $program->applications()->where('status', 'in_review')->count(),
            'approved' => $program->applications()->where('status', 'approved')->count(),
            'rejected' => $program->applications()->where('status', 'rejected')->count(),
        ];
        
        return view('admin.programs.show', compact('program', 'applications', 'applicationStats'));
    }

    /**
     * Muestra el formulario para editar un programa.
     *
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function edit(Program $program)
    {
        $currencies = Currency::active()->orderBy('code')->get();
        return view('admin.programs.edit', compact('program', 'currencies'));
    }

    /**
     * Actualiza un programa en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProgramRequest $request, Program $program)
    {
        
        $program->name = $request->name;
        $program->description = $request->description;
        $program->country = $request->country;
        $program->category = $request->category;
        $program->location = $request->location;
        $program->start_date = $request->start_date;
        $program->end_date = $request->end_date;
        $program->application_deadline = $request->application_deadline;
        $program->duration = $request->duration;
        $program->credits = $request->credits;
        $program->capacity = $request->capacity;
        $program->cost = $request->cost;
        $program->currency_id = $request->currency_id;
        $program->image_url = $request->image_url;
        $program->is_active = $request->is_active;
        
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($program->image_url && str_contains($program->image_url, 'storage')) {
                $oldPath = str_replace('/storage/', '', $program->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            
            $imagePath = $request->file('image')->store('programs', 'public');
            $program->image_url = Storage::url($imagePath);
        }
        
        $program->save();
        
        return redirect()->route('admin.programs.index')
            ->with('success', 'Programa actualizado correctamente.');
    }

    /**
     * Elimina un programa de la base de datos.
     *
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function destroy(Program $program)
    {
        // Verificar si hay aplicaciones asociadas
        if ($program->applications()->count() > 0) {
            return redirect()->route('admin.programs.index')
                ->with('error', 'No se puede eliminar el programa porque tiene solicitudes asociadas.');
        }
        
        // Eliminar imagen si existe
        if ($program->image) {
            Storage::disk('public')->delete($program->image);
        }
        
        $program->delete();
        
        return redirect()->route('admin.programs.index')
            ->with('success', 'Programa eliminado correctamente.');
    }
    
    /**
     * Exporta la lista de programas a un archivo CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // Implementación básica para demostración
        return redirect()->route('admin.programs.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente.');
    }
}
