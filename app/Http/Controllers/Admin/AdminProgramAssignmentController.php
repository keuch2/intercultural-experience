<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\User;
use App\Models\ProgramAssignment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminProgramAssignmentController extends Controller
{
    /**
     * Display a listing of the assignments.
     */
    public function index(Request $request)
    {
        $query = ProgramAssignment::with(['user', 'program', 'assignedBy']);

        // Filtros
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_search')) {
            $search = $request->user_search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('assigned_by')) {
            $query->where('assigned_by', $request->assigned_by);
        }

        if ($request->filled('priority')) {
            $query->where('is_priority', $request->priority === 'yes');
        }

        if ($request->filled('overdue')) {
            if ($request->overdue === 'yes') {
                $query->where('application_deadline', '<', Carbon::now())
                      ->where('status', ProgramAssignment::STATUS_ASSIGNED);
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'assigned_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $assignments = $query->paginate(20);

        // Datos para filtros
        $programs = Program::active()->orderBy('name')->get();
        $admins = User::where('role', 'admin')->orderBy('name')->get();
        $statuses = ProgramAssignment::STATUSES;

        // Estadísticas generales
        $stats = ProgramAssignment::getAssignmentStats();

        return view('admin.assignments.index', compact(
            'assignments', 'programs', 'admins', 'statuses', 'stats'
        ));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create(Request $request)
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        $programs = Program::active()->orderBy('name')->get();
        
        // Si viene un user_id o program_id preseleccionado
        $selectedUser = $request->filled('user_id') ? 
            User::find($request->user_id) : null;
        $selectedProgram = $request->filled('program_id') ? 
            Program::find($request->program_id) : null;

        return view('admin.assignments.create', compact(
            'users', 'programs', 'selectedUser', 'selectedProgram'
        ));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'assignment_notes' => 'nullable|string|max:1000',
            'application_deadline' => 'nullable|date|after:today',
            'can_apply' => 'boolean',
            'is_priority' => 'boolean',
            'send_notification' => 'boolean',
        ]);

        // Verificar que el usuario no tenga ya una asignación para este programa
        $existingAssignment = ProgramAssignment::where('user_id', $request->user_id)
            ->where('program_id', $request->program_id)
            ->first();

        if ($existingAssignment) {
            return back()->withErrors([
                'user_id' => 'Este usuario ya tiene una asignación para este programa.'
            ])->withInput();
        }

        // Verificar capacidad del programa
        $program = Program::find($request->program_id);
        if (!$program->canAcceptMoreParticipants()) {
            return back()->withErrors([
                'program_id' => 'Este programa ya alcanzó su capacidad máxima.'
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            // Crear la asignación
            $assignment = ProgramAssignment::createAssignment(
                $request->user_id,
                $request->program_id,
                Auth::id(),
                [
                    'assignment_notes' => $request->assignment_notes,
                    'application_deadline' => $request->application_deadline,
                    'can_apply' => $request->boolean('can_apply', true),
                    'is_priority' => $request->boolean('is_priority', false),
                ]
            );

            // Enviar notificación si se solicitó
            if ($request->boolean('send_notification', true)) {
                $this->sendAssignmentNotification($assignment);
            }

            DB::commit();

            return redirect()->route('admin.assignments.index')
                ->with('success', 'Programa asignado exitosamente al usuario.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors([
                'error' => 'Error al crear la asignación: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Display the specified assignment.
     */
    public function show(ProgramAssignment $assignment)
    {
        $assignment->load(['user', 'program', 'assignedBy', 'application']);
        
        // Obtener estadísticas del programa
        $programStats = $assignment->program->getAssignmentStats();
        
        // Obtener historial de cambios (si existe aplicación)
        $applicationHistory = [];
        if ($assignment->application) {
            // Aquí podrías agregar lógica para obtener el historial de la aplicación
        }

        return view('admin.assignments.show', compact(
            'assignment', 'programStats', 'applicationHistory'
        ));
    }

    /**
     * Show the form for editing the assignment.
     */
    public function edit(ProgramAssignment $assignment)
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        $programs = Program::active()->orderBy('name')->get();

        return view('admin.assignments.edit', compact(
            'assignment', 'users', 'programs'
        ));
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, ProgramAssignment $assignment)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(ProgramAssignment::STATUSES)),
            'admin_notes' => 'nullable|string|max:1000',
            'application_deadline' => 'nullable|date',
            'can_apply' => 'boolean',
            'is_priority' => 'boolean',
            'send_notification' => 'boolean',
        ]);

        $oldStatus = $assignment->status;
        $newStatus = $request->status;

        try {
            DB::beginTransaction();

            // Actualizar datos básicos
            $assignment->update([
                'status' => $newStatus,
                'admin_notes' => $request->admin_notes,
                'application_deadline' => $request->application_deadline,
                'can_apply' => $request->boolean('can_apply', true),
                'is_priority' => $request->boolean('is_priority', false),
            ]);

            // Actualizar fechas según el estado
            if ($oldStatus !== $newStatus) {
                switch ($newStatus) {
                    case ProgramAssignment::STATUS_UNDER_REVIEW:
                        $assignment->markUnderReview();
                        break;
                    case ProgramAssignment::STATUS_ACCEPTED:
                        $assignment->markAsAccepted($request->admin_notes);
                        break;
                    case ProgramAssignment::STATUS_REJECTED:
                        $assignment->markAsRejected($request->admin_notes);
                        break;
                    case ProgramAssignment::STATUS_COMPLETED:
                        $assignment->markAsCompleted();
                        break;
                    case ProgramAssignment::STATUS_CANCELLED:
                        $assignment->cancel($request->admin_notes);
                        break;
                }

                // Enviar notificación de cambio de estado
                if ($request->boolean('send_notification', true)) {
                    $this->sendStatusChangeNotification($assignment, $oldStatus, $newStatus);
                }
            }

            DB::commit();

            return redirect()->route('admin.assignments.show', $assignment)
                ->with('success', 'Asignación actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors([
                'error' => 'Error al actualizar la asignación: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(ProgramAssignment $assignment)
    {
        if (!$assignment->canBeCancelled()) {
            return back()->withErrors([
                'error' => 'Esta asignación no puede ser eliminada en su estado actual.'
            ]);
        }

        try {
            DB::beginTransaction();

            // Enviar notificación de cancelación
            $this->sendCancellationNotification($assignment);

            // Eliminar la asignación
            $assignment->delete();

            DB::commit();

            return redirect()->route('admin.assignments.index')
                ->with('success', 'Asignación eliminada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors([
                'error' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Asignar programas masivamente
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'assignment_notes' => 'nullable|string|max:1000',
            'application_deadline' => 'nullable|date|after:today',
            'can_apply' => 'boolean',
            'is_priority' => 'boolean',
            'send_notifications' => 'boolean',
        ]);

        $program = Program::find($request->program_id);
        $userIds = $request->user_ids;
        
        $successCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            foreach ($userIds as $userId) {
                try {
                    // Verificar si ya existe asignación
                    $existingAssignment = ProgramAssignment::where('user_id', $userId)
                        ->where('program_id', $request->program_id)
                        ->first();

                    if ($existingAssignment) {
                        $user = User::find($userId);
                        $errors[] = "El usuario {$user->name} ya tiene asignación para este programa.";
                        continue;
                    }

                    // Crear asignación
                    $assignment = ProgramAssignment::createAssignment(
                        $userId,
                        $request->program_id,
                        Auth::id(),
                        [
                            'assignment_notes' => $request->assignment_notes,
                            'application_deadline' => $request->application_deadline,
                            'can_apply' => $request->boolean('can_apply', true),
                            'is_priority' => $request->boolean('is_priority', false),
                        ]
                    );

                    // Enviar notificación
                    if ($request->boolean('send_notifications', true)) {
                        $this->sendAssignmentNotification($assignment);
                    }

                    $successCount++;

                } catch (\Exception $e) {
                    $user = User::find($userId);
                    $errors[] = "Error asignando a {$user->name}: {$e->getMessage()}";
                }
            }

            DB::commit();

            $message = "Asignación masiva completada: {$successCount} usuarios asignados exitosamente.";
            if (!empty($errors)) {
                $message .= " Errores: " . implode(', ', $errors);
            }

            return redirect()->route('admin.assignments.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error en asignación masiva: ' . $e->getMessage()]);
        }
    }

    /**
     * API para obtener estadísticas de asignaciones
     */
    public function getStats(Request $request)
    {
        $programId = $request->get('program_id');
        $stats = ProgramAssignment::getAssignmentStats($programId);
        
        return response()->json($stats);
    }

    /**
     * Private methods
     */
    private function sendAssignmentNotification(ProgramAssignment $assignment)
    {
        Notification::create([
            'user_id' => $assignment->user_id,
            'title' => 'Nuevo Programa Asignado',
            'message' => "Te han asignado el programa: {$assignment->program->name}. " .
                        "Puedes comenzar tu aplicación desde la app móvil.",
            'type' => 'program_assignment',
            'data' => [
                'assignment_id' => $assignment->id,
                'program_id' => $assignment->program_id,
                'program_name' => $assignment->program->name,
                'can_apply' => $assignment->can_apply,
                'deadline' => $assignment->application_deadline?->format('Y-m-d'),
            ],
            'read_at' => null,
        ]);
    }

    private function sendStatusChangeNotification(ProgramAssignment $assignment, $oldStatus, $newStatus)
    {
        $statusMessages = [
            ProgramAssignment::STATUS_UNDER_REVIEW => 'Tu aplicación está siendo revisada.',
            ProgramAssignment::STATUS_ACCEPTED => '¡Felicitaciones! Has sido aceptado en el programa.',
            ProgramAssignment::STATUS_REJECTED => 'Lamentablemente, tu aplicación no fue aceptada.',
            ProgramAssignment::STATUS_COMPLETED => '¡Has completado exitosamente el programa!',
            ProgramAssignment::STATUS_CANCELLED => 'La asignación del programa ha sido cancelada.',
        ];

        $message = $statusMessages[$newStatus] ?? "El estado de tu asignación ha cambiado.";

        Notification::create([
            'user_id' => $assignment->user_id,
            'title' => 'Estado de Programa Actualizado',
            'message' => "Programa: {$assignment->program->name}. {$message}",
            'type' => 'assignment_status_change',
            'data' => [
                'assignment_id' => $assignment->id,
                'program_id' => $assignment->program_id,
                'program_name' => $assignment->program->name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'admin_notes' => $assignment->admin_notes,
            ],
            'read_at' => null,
        ]);
    }

    private function sendCancellationNotification(ProgramAssignment $assignment)
    {
        Notification::create([
            'user_id' => $assignment->user_id,
            'title' => 'Asignación de Programa Cancelada',
            'message' => "La asignación del programa {$assignment->program->name} ha sido cancelada.",
            'type' => 'assignment_cancelled',
            'data' => [
                'assignment_id' => $assignment->id,
                'program_id' => $assignment->program_id,
                'program_name' => $assignment->program->name,
                'admin_notes' => $assignment->admin_notes,
            ],
            'read_at' => null,
        ]);
    }
}
