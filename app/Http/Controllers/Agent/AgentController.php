<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use App\Models\ProgramAssignment;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Controlador para el panel de agentes
 * Permite a los agentes gestionar participantes y asignarlos a programas
 */
class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('agent');
    }

    /**
     * Dashboard del agente
     */
    public function dashboard()
    {
        $agent = Auth::user();
        
        // Métricas del agente
        $participantsCount = User::where('created_by_agent_id', $agent->id)->count();
        
        $activeApplicationsCount = Application::whereHas('user', function ($query) use ($agent) {
            $query->where('created_by_agent_id', $agent->id);
        })->where('status', 'approved')->count();
        
        $pendingApplicationsCount = Application::whereHas('user', function ($query) use ($agent) {
            $query->where('created_by_agent_id', $agent->id);
        })->where('status', 'pending')->count();
        
        $programsCount = ProgramAssignment::whereHas('user', function ($query) use ($agent) {
            $query->where('created_by_agent_id', $agent->id);
        })->where('status', 'active')->distinct('program_id')->count();
        
        // Participantes recientes
        $recentParticipants = User::where('created_by_agent_id', $agent->id)
            ->latest()
            ->take(5)
            ->get();
        
        // Aplicaciones pendientes
        $pendingApplications = Application::whereHas('user', function ($query) use ($agent) {
            $query->where('created_by_agent_id', $agent->id);
        })
        ->where('status', 'pending')
        ->with(['user', 'program'])
        ->latest()
        ->take(5)
        ->get();
        
        return view('agent.dashboard', compact(
            'participantsCount',
            'activeApplicationsCount',
            'pendingApplicationsCount',
            'programsCount',
            'recentParticipants',
            'pendingApplications'
        ));
    }

    /**
     * Listar participantes creados por el agente
     */
    public function myParticipants(Request $request)
    {
        $agent = Auth::user();
        
        $query = User::where('created_by_agent_id', $agent->id)
            ->where('role', 'user');
        
        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        
        $participants = $query->with('programAssignments.program')
            ->latest()
            ->paginate(15);
        
        // Países únicos para filtro
        $countries = User::where('created_by_agent_id', $agent->id)
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country');
        
        return view('agent.participants.index', compact('participants', 'countries'));
    }

    /**
     * Formulario para crear nuevo participante
     */
    public function createParticipant()
    {
        $programs = Program::where('is_active', true)
            ->where('available_slots', '>', 0)
            ->orderBy('name')
            ->get();
        
        return view('agent.participants.create', compact('programs'));
    }

    /**
     * Guardar nuevo participante
     */
    public function storeParticipant(Request $request)
    {
        $agent = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'nationality' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'address' => 'nullable|string',
            'academic_level' => 'nullable|string|max:100',
            'english_level' => 'nullable|string|max:50',
            'program_id' => 'nullable|exists:programs,id'
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'Este email ya está registrado',
            'phone.required' => 'El teléfono es obligatorio',
            'country.required' => 'El país es obligatorio',
            'nationality.required' => 'La nacionalidad es obligatoria',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy',
        ]);
        
        // Generar contraseña temporal
        $temporaryPassword = Str::random(12);
        
        // Crear participante
        $participant = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($temporaryPassword),
            'role' => 'user',
            'created_by_agent_id' => $agent->id,
            'phone' => $validated['phone'],
            'country' => $validated['country'],
            'city' => $validated['city'] ?? null,
            'nationality' => $validated['nationality'],
            'birth_date' => $validated['birth_date'],
            'address' => $validated['address'] ?? null,
            'academic_level' => $validated['academic_level'] ?? null,
            'english_level' => $validated['english_level'] ?? null,
        ]);
        
        // Asignar a programa si se seleccionó
        if ($request->filled('program_id')) {
            $program = Program::findOrFail($request->program_id);
            
            // Verificar disponibilidad
            if ($program->available_slots > 0) {
                ProgramAssignment::create([
                    'user_id' => $participant->id,
                    'program_id' => $program->id,
                    'assigned_by' => $agent->id,
                    'status' => 'active',
                    'notes' => 'Asignado al crear el participante'
                ]);
                
                // Decrementar slots disponibles
                $program->decrement('available_slots');
            }
        }
        
        // Disparar evento de usuario creado
        event(new \App\Events\UserCreated($participant, $temporaryPassword, $agent));
        
        return redirect()
            ->route('agent.participants.index')
            ->with('success', "Participante {$participant->name} creado exitosamente. Contraseña temporal: {$temporaryPassword}");
    }

    /**
     * Ver detalle de participante
     */
    public function showParticipant($id)
    {
        $agent = Auth::user();
        
        $participant = User::where('id', $id)
            ->where('created_by_agent_id', $agent->id)
            ->where('role', 'user')
            ->with(['programAssignments.program', 'applications.program'])
            ->firstOrFail();
        
        // Authorize
        $this->authorize('view', $participant);
        
        return view('agent.participants.show', compact('participant'));
    }

    /**
     * Formulario para asignar programa a participante
     */
    public function assignProgramForm($participantId)
    {
        $agent = Auth::user();
        
        $participant = User::where('id', $participantId)
            ->where('created_by_agent_id', $agent->id)
            ->where('role', 'user')
            ->firstOrFail();
        
        // Authorize
        $this->authorize('assignProgram', $participant);
        
        // Programas disponibles (que el participante no tenga asignados)
        $assignedProgramIds = $participant->programAssignments()->pluck('program_id');
        
        $programs = Program::where('is_active', true)
            ->where('available_slots', '>', 0)
            ->whereNotIn('id', $assignedProgramIds)
            ->orderBy('name')
            ->get();
        
        return view('agent.participants.assign-program', compact('participant', 'programs'));
    }

    /**
     * Asignar programa a participante
     */
    public function assignProgram(Request $request, $participantId)
    {
        $agent = Auth::user();
        
        $participant = User::where('id', $participantId)
            ->where('created_by_agent_id', $agent->id)
            ->where('role', 'user')
            ->firstOrFail();
        
        // Authorize
        $this->authorize('assignProgram', $participant);
        
        $validated = $request->validate([
            'program_id' => [
                'required',
                'exists:programs,id',
                Rule::unique('program_assignments')->where(function ($query) use ($participantId) {
                    return $query->where('user_id', $participantId);
                }),
            ],
            'notes' => 'nullable|string|max:1000'
        ], [
            'program_id.required' => 'Debe seleccionar un programa',
            'program_id.unique' => 'Este participante ya está asignado a este programa',
        ]);
        
        $program = Program::findOrFail($validated['program_id']);
        
        // Verificar disponibilidad
        if ($program->available_slots <= 0) {
            return back()->with('error', 'Este programa no tiene cupos disponibles.');
        }
        
        // Crear asignación
        $assignment = ProgramAssignment::create([
            'user_id' => $participant->id,
            'program_id' => $program->id,
            'assigned_by' => $agent->id,
            'status' => 'active',
            'notes' => $validated['notes'] ?? null
        ]);
        
        // Decrementar slots disponibles
        $program->decrement('available_slots');
        
        // Disparar evento de asignación de programa
        event(new \App\Events\ParticipantAssignedToProgram($participant, $program, $assignment, $agent));
        
        return redirect()
            ->route('agent.participants.show', $participant->id)
            ->with('success', "Participante asignado exitosamente al programa {$program->name}");
    }
}
