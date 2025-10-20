<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Controlador para la gestión de agentes en el panel administrativo
 */
class AdminAgentController extends Controller
{
    /**
     * Listar todos los agentes
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'agent');
        
        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $agents = $query->withCount('createdParticipants')
            ->latest()
            ->paginate(15);
        
        return view('admin.agents.index', compact('agents'));
    }

    /**
     * Mostrar formulario para crear nuevo agente
     */
    public function create()
    {
        return view('admin.agents.create');
    }

    /**
     * Guardar nuevo agente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:100',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'Este email ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'phone.required' => 'El teléfono es obligatorio',
            'country.required' => 'El país es obligatorio',
        ]);
        
        $agent = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'agent',
            'phone' => $validated['phone'],
            'country' => $validated['country'],
            'city' => $validated['city'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'email_verified_at' => now(),
        ]);
        
        // TODO: Enviar email con credenciales (se implementará en Épica 2)
        
        return redirect()
            ->route('admin.agents.index')
            ->with('success', "Agente {$agent->name} creado exitosamente.");
    }

    /**
     * Mostrar detalle de agente
     */
    public function show($id)
    {
        $agent = User::where('role', 'agent')
            ->with(['createdParticipants.programAssignments.program'])
            ->withCount('createdParticipants')
            ->findOrFail($id);
        
        return view('admin.agents.show', compact('agent'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $agent = User::where('role', 'agent')->findOrFail($id);
        
        return view('admin.agents.edit', compact('agent'));
    }

    /**
     * Actualizar agente
     */
    public function update(Request $request, $id)
    {
        $agent = User::where('role', 'agent')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $agent->id,
            'phone' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:100',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'Este email ya está en uso',
            'phone.required' => 'El teléfono es obligatorio',
            'country.required' => 'El país es obligatorio',
        ]);
        
        $agent->update($validated);
        
        return redirect()
            ->route('admin.agents.show', $agent->id)
            ->with('success', 'Agente actualizado exitosamente');
    }

    /**
     * Eliminar agente
     */
    public function destroy($id)
    {
        $agent = User::where('role', 'agent')->findOrFail($id);
        
        // Verificar si tiene participantes creados
        $participantsCount = $agent->createdParticipants()->count();
        
        if ($participantsCount > 0) {
            return back()->with('error', "No se puede eliminar el agente porque tiene {$participantsCount} participantes creados. Los participantes se mantendrán en el sistema.");
        }
        
        $agent->delete();
        
        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Agente eliminado exitosamente');
    }

    /**
     * Resetear contraseña de agente
     */
    public function resetPassword($id)
    {
        $agent = User::where('role', 'agent')->findOrFail($id);
        
        $temporaryPassword = Str::random(12);
        $agent->update([
            'password' => Hash::make($temporaryPassword)
        ]);
        
        // TODO: Enviar email con nueva contraseña (se implementará en Épica 2)
        
        return redirect()
            ->route('admin.agents.show', $agent->id)
            ->with('success', "Contraseña reseteada exitosamente. Nueva contraseña temporal: {$temporaryPassword}");
    }
}
