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
        $query = User::where('role', 'user')->with(['applications.program', 'englishEvaluations']);
        
        // Filtrar por categoría de programa (IE o YFU)
        if ($request->filled('program_category')) {
            $category = $request->program_category;
            $query->whereHas('applications.program', function($q) use ($category) {
                $q->where('main_category', $category);
            });
        }
        
        // Búsqueda general
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('nationality', 'like', "%{$search}%");
            });
        }
        
        // Filtro por país
        if ($request->filled('country')) {
            $query->where('country', 'like', "%{$request->country}%");
        }
        
        // Filtro por ciudad
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }
        
        // Filtro por programa específico
        if ($request->filled('program_id')) {
            $query->whereHas('applications', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }
        
        // Filtro por estado de aplicación
        if ($request->filled('application_status')) {
            $query->whereHas('applications', function($q) use ($request) {
                $q->where('status', $request->application_status);
            });
        }
        
        // Filtro por nivel de inglés
        if ($request->filled('english_level')) {
            $query->whereHas('englishEvaluations', function($q) use ($request) {
                $q->where('cefr_level', $request->english_level)
                  ->whereRaw('id IN (SELECT MAX(id) FROM english_evaluations GROUP BY user_id)');
            });
        }
        
        // Filtro por género
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        
        // Filtro por rango de edad
        if ($request->filled('age_from')) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?', [$request->age_from]);
        }
        if ($request->filled('age_to')) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= ?', [$request->age_to]);
        }
        
        // Filtro por fecha de registro
        if ($request->filled('registered_from')) {
            $query->whereDate('created_at', '>=', $request->registered_from);
        }
        if ($request->filled('registered_to')) {
            $query->whereDate('created_at', '<=', $request->registered_to);
        }
        
        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $participants = $query->paginate(15)->appends($request->except('page'));
        
        // Datos para filtros
        $programs = Program::select('id', 'name', 'main_category')->orderBy('name')->get();
        $countries = User::where('role', 'user')->distinct()->pluck('country')->filter()->sort()->values();
        $cities = User::where('role', 'user')->distinct()->pluck('city')->filter()->sort()->values();
        
        $programCategory = $request->program_category;
        
        return view('admin.participants.index', compact('participants', 'programCategory', 'programs', 'countries', 'cities'));
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
        
        // Cargar todas las relaciones necesarias incluyendo las nuevas
        $participant->load([
            'applications.program',
            'points',
            'supportTickets',
            'emergencyContacts',
            'workExperiences'
        ]);
        
        // Calcular estadísticas de aplicaciones
        $applicationStats = [
            'total' => $participant->applications->count(),
            'pending' => $participant->applications->where('status', 'pending')->count(),
            'approved' => $participant->applications->where('status', 'approved')->count(),
            'rejected' => $participant->applications->where('status', 'rejected')->count(),
        ];
        
        // Calcular total de puntos
        $totalPoints = $participant->points->sum('points');
        
        return view('admin.participants.show', compact('participant', 'applicationStats', 'totalPoints'));
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
        
        // Cargar relaciones incluyendo las nuevas
        $participant->load([
            'applications.program',
            'emergencyContacts',
            'workExperiences'
        ]);
        
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
            'academic_level' => ['nullable', 'in:bachiller,licenciatura,maestria,posgrado,doctorado,high_school,bachelor,master,phd'],
            'english_level' => ['nullable', 'in:basico,intermedio,avanzado,nativo,A1,A2,B1,B1+,B2,C1,C2'],
            'bio' => ['nullable', 'string'],
            'program_id' => ['nullable', 'exists:programs,id'],
            // Campos de salud
            'blood_type' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'health_insurance' => ['nullable', 'string', 'max:100'],
            'health_insurance_number' => ['nullable', 'string', 'max:100'],
            'medical_conditions' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'emergency_medical_contact' => ['nullable', 'string', 'max:100'],
            'emergency_medical_phone' => ['nullable', 'string', 'max:50'],
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
        
        // Actualizar participante con todos los campos incluyendo salud
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
        
        return redirect()->route('admin.participants.show', $participant->id)
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

    /**
     * Export participants to Excel
     */
    public function export(Request $request)
    {
        // Aplicar mismos filtros que index
        $query = User::where('role', 'user')->with(['applications.program', 'englishEvaluations']);
        
        if ($request->filled('program_category')) {
            $query->whereHas('applications.program', function($q) use ($request) {
                $q->where('main_category', $request->program_category);
            });
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('english_level')) {
            $query->whereHas('englishEvaluations', function($q) use ($request) {
                $q->where('cefr_level', $request->english_level);
            });
        }
        
        $participants = $query->get();
        
        // Crear archivo CSV
        $filename = 'participants_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        // Headers
        fputcsv($handle, [
            'ID',
            'Nombre',
            'Email',
            'Teléfono',
            'País',
            'Ciudad',
            'Género',
            'Fecha Nacimiento',
            'Nivel Inglés',
            'Programas',
            'Fecha Registro',
        ]);
        
        // Datos
        foreach ($participants as $participant) {
            $lastEval = $participant->englishEvaluations->sortByDesc('created_at')->first();
            $programs = $participant->applications->pluck('program.name')->unique()->join(', ');
            
            fputcsv($handle, [
                $participant->id,
                $participant->name,
                $participant->email,
                $participant->phone,
                $participant->country,
                $participant->city,
                $participant->gender,
                $participant->date_of_birth ? $participant->date_of_birth->format('Y-m-d') : '',
                $lastEval ? $lastEval->cefr_level : 'N/A',
                $programs,
                $participant->created_at->format('Y-m-d H:i:s'),
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
