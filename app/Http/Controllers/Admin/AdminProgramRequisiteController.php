<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\ProgramRequisite;
use App\Models\UserProgramRequisite;
use App\Models\Application;
use App\Models\Currency;
use Illuminate\Support\Facades\Storage;

class AdminProgramRequisiteController extends Controller
{
    /**
     * Muestra la lista de requisitos de un programa.
     *
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function index(Program $program)
    {
        $requisites = $program->requisites()->orderBy('order')->get();
        return view('admin.programs.requisites.index', compact('program', 'requisites'));
    }

    /**
     * Muestra el formulario para crear un nuevo requisito.
     *
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function create(Program $program)
    {
        $currencies = Currency::all();
        return view('admin.programs.requisites.create', compact('program', 'currencies'));
    }

    /**
     * Almacena un nuevo requisito en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Program $program)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:document,action,payment',
            'is_required' => 'boolean',
            'order' => 'integer|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);
        
        $requisite = new ProgramRequisite();
        $requisite->program_id = $program->id;
        $requisite->name = $request->name;
        $requisite->description = $request->description;
        $requisite->type = $request->type;
        $requisite->is_required = $request->has('is_required');
        $requisite->order = $request->order ?? 0;
        
        // Solo guardar monto y moneda si el tipo es payment
        if ($request->type === 'payment') {
            $requisite->payment_amount = $request->payment_amount;
            $requisite->currency_id = $request->currency_id;
        }
        
        $requisite->save();
        
        // Crear este requisito para todas las aplicaciones existentes del programa
        $applications = Application::where('program_id', $program->id)->get();
        $createdCount = 0;
        
        foreach ($applications as $application) {
            try {
                UserProgramRequisite::create([
                    'application_id' => $application->id,
                    'program_requisite_id' => $requisite->id,
                    'status' => 'pending'
                ]);
                $createdCount++;
                
                \Log::info('UserProgramRequisite creado', [
                    'application_id' => $application->id,
                    'program_requisite_id' => $requisite->id,
                    'user' => $application->user->name
                ]);
            } catch (\Exception $e) {
                \Log::error('Error al crear UserProgramRequisite', [
                    'application_id' => $application->id,
                    'program_requisite_id' => $requisite->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $message = 'Requisito creado correctamente.';
        if ($createdCount > 0) {
            $message .= " Se asignó a {$createdCount} aplicación(es) existente(s).";
        }
        
        return redirect()->route('admin.programs.requisites.index', $program->id)
            ->with('success', $message);
    }

    /**
     * Muestra el formulario para editar un requisito.
     *
     * @param  \App\Models\Program  $program
     * @param  \App\Models\ProgramRequisite  $requisite
     * @return \Illuminate\Http\Response
     */
    public function edit(Program $program, ProgramRequisite $requisite)
    {
        $currencies = Currency::all();
        return view('admin.programs.requisites.edit', compact('program', 'requisite', 'currencies'));
    }

    /**
     * Actualiza un requisito en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Program  $program
     * @param  \App\Models\ProgramRequisite  $requisite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Program $program, ProgramRequisite $requisite)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:document,action,payment',
            'is_required' => 'boolean',
            'order' => 'integer|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);
        
        $requisite->name = $request->name;
        $requisite->description = $request->description;
        $requisite->type = $request->type;
        $requisite->is_required = $request->has('is_required');
        $requisite->order = $request->order ?? 0;
        
        // Solo guardar monto y moneda si el tipo es payment
        if ($request->type === 'payment') {
            $requisite->payment_amount = $request->payment_amount;
            $requisite->currency_id = $request->currency_id;
        } else {
            // Limpiar campos si cambia de tipo payment a otro
            $requisite->payment_amount = null;
            $requisite->currency_id = null;
        }
        
        $requisite->save();
        
        return redirect()->route('admin.programs.requisites.index', $program->id)
            ->with('success', 'Requisito actualizado correctamente.');
    }

    /**
     * Elimina un requisito de la base de datos.
     *
     * @param  \App\Models\Program  $program
     * @param  \App\Models\ProgramRequisite  $requisite
     * @return \Illuminate\Http\Response
     */
    public function destroy(Program $program, ProgramRequisite $requisite)
    {
        // Eliminar también todos los requisitos de usuario asociados
        UserProgramRequisite::where('program_requisite_id', $requisite->id)->delete();
        
        $requisite->delete();
        
        return redirect()->route('admin.programs.requisites.index', $program->id)
            ->with('success', 'Requisito eliminado correctamente.');
    }
    
    /**
     * Actualiza el orden de los requisitos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request, Program $program)
    {
        $request->validate([
            'requisites' => 'required|array',
            'requisites.*.id' => 'required|exists:program_requisites,id',
            'requisites.*.order' => 'required|integer|min:0',
        ]);
        
        foreach ($request->requisites as $item) {
            ProgramRequisite::where('id', $item['id'])->update(['order' => $item['order']]);
        }
        
        return response()->json(['success' => true]);
    }
}
