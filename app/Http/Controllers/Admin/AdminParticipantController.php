<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AdminParticipantController extends Controller
{
    /**
     * Display a listing of participants (only users with role 'user').
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user');
        
        // Filtrar por categoría de programa (IE o YFU)
        if ($request->has('program_category') && $request->input('program_category') != '') {
            $category = $request->input('program_category');
            $query->whereHas('applications.program', function($q) use ($category) {
                $q->where('main_category', $category);
            });
        }
        
        // Aplicar filtros si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('country') && $request->input('country') != '') {
            $query->where('country', 'like', "%{$request->input('country')}%");
        }
        
        $participants = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Pasar la categoría a la vista para mostrar el título correcto
        $programCategory = $request->input('program_category');
        
        return view('admin.participants.index', compact('participants', 'programCategory'));
    }

    /**
     * Show the form for creating a new participant.
     */
    public function create()
    {
        $programs = Program::where('is_active', true)
            ->orderBy('main_category')
            ->orderBy('name')
            ->get();
        
        return view('admin.participants.create', compact('programs'));
    }

    /**
     * Store a newly created participant in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'academic_level' => ['nullable', 'in:bachiller,licenciatura,maestria,posgrado,doctorado'],
            'english_level' => ['nullable', 'in:basico,intermedio,avanzado,nativo'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'program_id' => ['nullable', 'exists:programs,id'],
        ]);
        
        $data = $request->except(['password', 'password_confirmation', 'profile_photo', 'program_id']);
        $data['password'] = Hash::make($request->password);
        $data['role'] = 'user'; // Always set role as 'user' for participants
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
        }
        
        $user = User::create($data);
        
        // Create application if program is assigned
        if ($request->filled('program_id')) {
            Application::create([
                'user_id' => $user->id,
                'program_id' => $request->program_id,
                'status' => 'pending',
                'application_date' => now(),
            ]);
        }
        
        return redirect()->route('admin.participants.index')
            ->with('success', 'Participante creado correctamente.');
    }

    /**
     * Display the specified participant.
     */
    public function show(User $participant)
    {
        // Verificar que sea un participante (role = user)
        if ($participant->role !== 'user') {
            abort(404);
        }
        
        $participant->load(['applications.program', 'points', 'supportTickets']);
        
        return view('admin.participants.show', compact('participant'));
    }

    /**
     * Show the form for editing the specified participant.
     */
    public function edit(User $participant)
    {
        // Verificar que sea un participante (role = user)
        if ($participant->role !== 'user') {
            abort(404);
        }
        
        // Obtener todos los programas activos
        $programs = Program::where('is_active', true)
            ->orderBy('main_category')
            ->orderBy('name')
            ->get();
        
        // Cargar aplicaciones del participante
        $participant->load('applications.program');
        
        return view('admin.participants.edit', compact('participant', 'programs'));
    }

    /**
     * Update the specified participant in storage.
     */
    public function update(Request $request, User $participant)
    {
        // Verificar que sea un participante (role = user)
        if ($participant->role !== 'user') {
            abort(404);
        }
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $participant->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'academic_level' => ['nullable', 'in:bachiller,licenciatura,maestria,posgrado,doctorado'],
            'english_level' => ['nullable', 'in:basico,intermedio,avanzado,nativo'],
            'program_id' => ['nullable', 'exists:programs,id'],
        ];
        
        // Solo validar contraseña si se proporciona
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::defaults()];
        }
        
        $request->validate($rules);
        
        $data = $request->except(['password', 'password_confirmation', 'program_id']);
        
        // Actualizar contraseña solo si se proporciona
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $participant->update($data);
        
        // Crear aplicación si se asigna un programa
        if ($request->filled('program_id')) {
            // Verificar si ya existe una aplicación para este programa
            $existingApplication = Application::where('user_id', $participant->id)
                ->where('program_id', $request->program_id)
                ->first();
            
            if (!$existingApplication) {
                Application::create([
                    'user_id' => $participant->id,
                    'program_id' => $request->program_id,
                    'status' => 'pending',
                    'submission_date' => now(),
                ]);
            }
        }
        
        return redirect()->route('admin.participants.index')
            ->with('success', 'Participante actualizado correctamente.');
    }

    /**
     * Remove the specified participant from storage.
     */
    public function destroy(User $participant)
    {
        // Verificar que sea un participante (role = user)
        if ($participant->role !== 'user') {
            abort(404);
        }
        
        $participant->delete();
        
        return redirect()->route('admin.participants.index')
            ->with('success', 'Participante eliminado correctamente.');
    }
}
