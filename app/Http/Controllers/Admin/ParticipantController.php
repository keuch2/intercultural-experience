<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantController extends Controller
{
    /**
     * Display a listing of participants (applications).
     */
    public function index(Request $request)
    {
        $query = Application::with(['user', 'program']);
        
        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%")
                  ->orWhere('passport_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('current_stage')) {
            $query->where('current_stage', $request->current_stage);
        }
        
        $participants = $query->orderBy('created_at', 'desc')->paginate(20);
        $programs = Program::active()->get();
        
        // Estadísticas
        $stats = [
            'total' => Application::count(),
            'approved' => Application::where('status', 'approved')->count(),
            'pending' => Application::where('status', 'pending')->count(),
            'in_review' => Application::where('status', 'in_review')->count(),
            'rejected' => Application::where('status', 'rejected')->count(),
        ];
        
        return view('admin.participants.index', compact('participants', 'programs', 'stats'));
    }

    /**
     * Show the form for creating a new participant.
     */
    public function create()
    {
        $programs = Program::active()->get();
        return view('admin.participants.create', compact('programs'));
    }

    /**
     * Store a newly created participant.
     */
    public function store(Request $request)
    {
        // Si es una aplicación simultánea (copiar de existente)
        $copyFromId = $request->input('copy_from_application');
        
        if ($copyFromId) {
            return $this->storeSimultaneousApplication($request, $copyFromId);
        }
        
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'cedula' => 'required|string|max:255',
            'passport_number' => 'required|string|max:255',
            'passport_expiry' => 'required|date|after:today',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'user_id' => 'nullable|exists:users,id',
        ]);
        
        // Si no hay user_id, crear usuario automáticamente
        if (!isset($validated['user_id'])) {
            // Generar email temporal
            $email = strtolower(str_replace(' ', '.', $validated['full_name'])) . '@temp.ie.com';
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = strtolower(str_replace(' ', '.', $validated['full_name'])) . $counter . '@temp.ie.com';
                $counter++;
            }
            
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $email,
                'password' => bcrypt('password'), // Contraseña temporal
                'role' => 'user',
            ]);
            
            $validated['user_id'] = $user->id;
        }
        
        // Obtener costos del programa
        $program = Program::find($validated['program_id']);
        $validated['total_cost'] = $program->cost ?? 0;
        $validated['status'] = 'pending';
        $validated['current_stage'] = 'registration';
        $validated['progress_percentage'] = 10;
        $validated['applied_at'] = now();
        $validated['is_current_program'] = true; // Primera aplicación es siempre actual
        
        $participant = Application::create($validated);
        
        return redirect()
            ->route('admin.participants.show', $participant)
            ->with('success', 'Participante creado exitosamente');
    }

    /**
     * Store a simultaneous application (applying to another program).
     */
    protected function storeSimultaneousApplication(Request $request, $copyFromId)
    {
        $sourceApplication = Application::with('program')->findOrFail($copyFromId);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'copy_basic_data' => 'nullable|boolean',
            'set_as_current' => 'nullable|boolean',
        ]);
        
        // Verificar que no exista ya una aplicación activa para este programa
        $existingApp = Application::where('user_id', $validated['user_id'])
            ->where('program_id', $validated['program_id'])
            ->whereIn('status', ['pending', 'in_review', 'approved'])
            ->first();
        
        if ($existingApp) {
            return back()->withErrors([
                'program_id' => 'Ya existe una aplicación activa para este programa.'
            ])->withInput();
        }
        
        $newApplicationData = [
            'user_id' => $validated['user_id'],
            'program_id' => $validated['program_id'],
            'status' => 'pending',
            'current_stage' => 'registration',
            'progress_percentage' => 0,
            'applied_at' => now(),
            'is_current_program' => $request->boolean('set_as_current'),
        ];
        
        // Copiar datos básicos si está marcado
        if ($request->boolean('copy_basic_data')) {
            $newApplicationData = array_merge($newApplicationData, [
                'full_name' => $sourceApplication->full_name,
                'birth_date' => $sourceApplication->birth_date,
                'cedula' => $sourceApplication->cedula,
                'passport_number' => $sourceApplication->passport_number,
                'passport_expiry' => $sourceApplication->passport_expiry,
                'phone' => $sourceApplication->phone,
                'address' => $sourceApplication->address,
                'city' => $sourceApplication->city,
                'country' => $sourceApplication->country,
            ]);
        }
        
        // Obtener costo del programa
        $program = Program::find($validated['program_id']);
        $newApplicationData['total_cost'] = $program->cost ?? 0;
        
        \DB::transaction(function () use ($newApplicationData, $request, $sourceApplication) {
            // Si se marca como actual, desmarcar las demás
            if ($request->boolean('set_as_current')) {
                Application::where('user_id', $newApplicationData['user_id'])
                    ->update(['is_current_program' => false]);
            }
            
            // Crear nueva aplicación
            $newApplication = Application::create($newApplicationData);
            
            return $newApplication;
        });
        
        $newApplication = Application::where('user_id', $validated['user_id'])
            ->where('program_id', $validated['program_id'])
            ->latest()
            ->first();
        
        return redirect()
            ->route('admin.participants.edit', $newApplication->id)
            ->with('success', 'Nueva aplicación creada exitosamente. Ahora completa los datos específicos del programa.');
    }

    /**
     * Display the specified participant.
     */
    public function show($id)
    {
        $participant = Application::with([
            'user', 
            'program', 
            'documents', 
            'requisites',
            'payments' => function($query) {
                $query->with(['currency', 'verifiedBy', 'createdBy'])
                      ->orderBy('payment_date', 'desc');
            }
        ])->findOrFail($id);
        
        // Cargar TODAS las solicitudes del usuario
        $allApplications = collect([]);
        if ($participant->user) {
            $allApplications = Application::with(['program', 'workTravelData', 'auPairData', 'teacherData'])
                ->where('user_id', $participant->user_id)
                ->orderBy('applied_at', 'desc')
                ->get();
        }
        
        $programs = Program::active()->get(); // Para crear nueva solicitud
        $currencies = \App\Models\Currency::where('is_active', true)->get(); // Para pagos
        
        return view('admin.participants.show', compact('participant', 'allApplications', 'programs', 'currencies'));
    }

    /**
     * Show the form for editing the participant.
     */
    public function edit($id)
    {
        $participant = Application::with(['program', 'workTravelData', 'auPairData', 'teacherData'])
            ->findOrFail($id);
        $programs = Program::active()->get();
        
        // Determinar formulario específico y datos según el programa
        $specificData = null;
        $formView = null;
        
        if ($participant->program) {
            switch($participant->program->subcategory) {
                case 'Work and Travel':
                    $specificData = $participant->workTravelData ?? new \App\Models\WorkTravelData();
                    $formView = 'work_travel';
                    break;
                case 'Au Pair':
                    $specificData = $participant->auPairData ?? new \App\Models\AuPairData();
                    $formView = 'au_pair';
                    break;
                case "Teacher's Program":
                    $specificData = $participant->teacherData ?? new \App\Models\TeacherData();
                    $formView = 'teacher';
                    break;
            }
        }
        
        // Pasar variables específicas según el formulario
        $viewData = compact('participant', 'programs', 'formView');
        
        if ($formView === 'work_travel') {
            $viewData['workTravelData'] = $specificData;
        } elseif ($formView === 'au_pair') {
            $viewData['auPairData'] = $specificData;
        } elseif ($formView === 'teacher') {
            $viewData['teacherData'] = $specificData;
        }
        
        return view('admin.participants.edit', $viewData);
    }

    /**
     * Update the specified participant.
     */
    public function update(Request $request, $id)
    {
        $participant = Application::with('program')->findOrFail($id);
        
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'cedula' => 'required|string|max:255',
            'passport_number' => 'required|string|max:255',
            'passport_expiry' => 'required|date|after:today',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'status' => 'sometimes|string|in:pending,in_review,approved,rejected,completed',
            'current_stage' => 'sometimes|string|in:registration,documentation,evaluation,in_review,approved,in_program,completed,withdrawn',
        ]);
        
        \DB::transaction(function () use ($request, $participant, $validated) {
            // Actualizar datos base del participante
            $participant->update($validated);
            
            // Actualizar datos específicos según el programa
            if ($participant->program) {
                switch($participant->program->subcategory) {
                    case 'Work and Travel':
                        if ($request->has('work_travel')) {
                            $participant->workTravelData()->updateOrCreate(
                                ['application_id' => $participant->id],
                                $request->input('work_travel')
                            );
                        }
                        break;
                        
                    case 'Au Pair':
                        if ($request->has('au_pair')) {
                            $auPairData = $request->input('au_pair');
                            
                            // Manejar archivos si existen
                            if ($request->hasFile('au_pair.photos')) {
                                $photos = [];
                                foreach ($request->file('au_pair.photos') as $photo) {
                                    $photos[] = $photo->store('au_pair/photos', 'public');
                                }
                                $auPairData['photos'] = $photos;
                            }
                            
                            $participant->auPairData()->updateOrCreate(
                                ['application_id' => $participant->id],
                                $auPairData
                            );
                        }
                        break;
                        
                    case "Teacher's Program":
                        if ($request->has('teacher')) {
                            $participant->teacherData()->updateOrCreate(
                                ['application_id' => $participant->id],
                                $request->input('teacher')
                            );
                        }
                        break;
                }
            }
        });
        
        return redirect()
            ->route('admin.participants.show', $participant)
            ->with('success', 'Participante actualizado exitosamente');
    }

    /**
     * Update participant status.
     */
    public function updateStatus(Request $request, $id)
    {
        $participant = Application::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|string|in:pending,in_review,approved,rejected,completed',
            'notes' => 'nullable|string',
        ]);
        
        $participant->update([
            'status' => $validated['status'],
        ]);
        
        return back()->with('success', 'Estado actualizado exitosamente');
    }

    /**
     * Remove the specified participant.
     */
    public function destroy($id)
    {
        $participant = Application::findOrFail($id);
        $participant->delete();
        
        return redirect()
            ->route('admin.participants.index')
            ->with('success', 'Participante eliminado exitosamente');
    }

    /**
     * Display program history for a participant.
     * Shows all applications (current and past) for the same user.
     */
    public function programHistory($id)
    {
        $participant = Application::with(['user', 'program'])->findOrFail($id);
        
        // Get all applications for this user, eager load relationships
        $participant->user->load([
            'applications.program',
            'applications.workTravelData',
            'applications.auPairData',
            'applications.teacherData'
        ]);
        
        return view('admin.participants.program-history', compact('participant'));
    }

    /**
     * Get specific program form via AJAX.
     * Returns HTML of the specific form based on program type.
     */
    public function getProgramForm($id, $formType)
    {
        try {
            // $id is a User ID (participant), not an Application ID
            $user = User::findOrFail($id);
            $application = Application::where('user_id', $user->id)
                ->latest()
                ->first();
            
            // Load existing data from application or create empty model
            switch($formType) {
                case 'work_travel':
                    $workTravelData = $application ? ($application->workTravelData ?? new \App\Models\WorkTravelData()) : new \App\Models\WorkTravelData();
                    return view('admin.participants.forms.work_travel', compact('workTravelData'))->render();
                    
                case 'au_pair':
                    $auPairData = $application ? ($application->auPairData ?? new \App\Models\AuPairData()) : new \App\Models\AuPairData();
                    return view('admin.participants.forms.au_pair', compact('auPairData'))->render();
                    
                case 'teacher':
                    $teacherData = $application ? ($application->teacherData ?? new \App\Models\TeacherData()) : new \App\Models\TeacherData();
                    return view('admin.participants.forms.teacher', compact('teacherData'))->render();
                    
                default:
                    return response('<div class="alert alert-warning">No hay formulario específico disponible para este programa.</div>', 200);
            }
        } catch (\Exception $e) {
            \Log::error('Error loading program form: ' . $e->getMessage());
            return response('<div class="alert alert-danger"><strong>Error:</strong> ' . $e->getMessage() . '</div>', 500);
        }
    }

    /**
     * Store a new emergency contact for a participant.
     */
    public function storeEmergencyContact(Request $request, $participantId)
    {
        $participant = Application::findOrFail($participantId);
        $user = User::findOrFail($participant->user_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'alternative_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'is_primary' => 'nullable|boolean',
        ]);

        $validated['user_id'] = $user->id;
        $validated['is_primary'] = $request->boolean('is_primary');

        if ($validated['is_primary']) {
            \App\Models\EmergencyContact::where('user_id', $user->id)->update(['is_primary' => false]);
        }

        \App\Models\EmergencyContact::create($validated);

        return redirect()->route('admin.participants.show', $participantId)
            ->with('success', 'Contacto de emergencia agregado correctamente.');
    }

    /**
     * Delete an emergency contact.
     */
    public function destroyEmergencyContact($contactId)
    {
        $contact = \App\Models\EmergencyContact::findOrFail($contactId);
        $user = $contact->user;
        $contact->delete();

        $participant = Application::where('user_id', $user->id)->latest()->first();

        return redirect()->route('admin.participants.show', $participant->id ?? 0)
            ->with('success', 'Contacto de emergencia eliminado correctamente.');
    }

    /**
     * Store a new work experience for a participant.
     */
    public function storeWorkExperience(Request $request, $participantId)
    {
        $participant = Application::findOrFail($participantId);
        $user = User::findOrFail($participant->user_id);

        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
            'reference_name' => 'nullable|string|max:255',
            'reference_phone' => 'nullable|string|max:50',
            'reference_email' => 'nullable|email|max:255',
        ]);

        $validated['user_id'] = $user->id;
        $validated['is_current'] = $request->boolean('is_current');

        if ($validated['is_current']) {
            $validated['end_date'] = null;
        }

        \App\Models\WorkExperience::create($validated);

        return redirect()->route('admin.participants.show', $participantId)
            ->with('success', 'Experiencia laboral agregada correctamente.');
    }

    /**
     * Delete a work experience.
     */
    public function destroyWorkExperience($experienceId)
    {
        $experience = \App\Models\WorkExperience::findOrFail($experienceId);
        $user = $experience->user;
        $experience->delete();

        $participant = Application::where('user_id', $user->id)->latest()->first();

        return redirect()->route('admin.participants.show', $participant->id ?? 0)
            ->with('success', 'Experiencia laboral eliminada correctamente.');
    }
}
