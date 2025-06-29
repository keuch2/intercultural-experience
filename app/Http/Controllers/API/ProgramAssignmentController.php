<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProgramAssignment;
use App\Models\Program;
use App\Models\Application;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgramAssignmentController extends Controller
{
    /**
     * Get all assignments for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = $user->programAssignments()
                         ->with(['program', 'program.currency']);

            // Filtros opcionales
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('active_only')) {
                $query->active();
            }

            $assignments = $query->orderBy('assigned_at', 'desc')->get();

            // Transformar datos para la app
            $transformedAssignments = $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'status' => $assignment->status,
                    'status_name' => $assignment->status_name,
                    'can_apply' => $assignment->can_user_apply,
                    'is_priority' => $assignment->is_priority,
                    'is_overdue' => $assignment->is_overdue,
                    'days_until_deadline' => $assignment->days_until_deadline,
                    'progress_percentage' => $assignment->progress_percentage,
                    'assigned_at' => $assignment->assigned_at->toISOString(),
                    'applied_at' => $assignment->applied_at?->toISOString(),
                    'application_deadline' => $assignment->application_deadline?->toDateString(),
                    'assignment_notes' => $assignment->assignment_notes,
                    'admin_notes' => $assignment->admin_notes,
                    'program' => [
                        'id' => $assignment->program->id,
                        'name' => $assignment->program->name,
                        'description' => $assignment->program->description,
                        'country' => $assignment->program->country,
                        'category' => $assignment->program->category,
                        'location' => $assignment->program->location,
                        'start_date' => $assignment->program->start_date?->toDateString(),
                        'end_date' => $assignment->program->end_date?->toDateString(),
                        'duration' => $assignment->program->duration,
                        'image_url' => $assignment->program->image_url,
                        'cost_display' => 'A Cotizar', // Como estableciste antes
                        'available_spots' => $assignment->program->getAvailableSpots(),
                    ],
                    'can_view_details' => true,
                    'can_apply_now' => $assignment->canBeAppliedTo(),
                ];
            });

            return response()->json([
                'success' => true,
                'assignments' => $transformedAssignments,
                'total' => $transformedAssignments->count(),
                'active_assignments' => $transformedAssignments->where('status', 'assigned')->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific assignment details
     */
    public function show($assignmentId)
    {
        try {
            $user = Auth::user();
            
            $assignment = $user->programAssignments()
                             ->with(['program', 'program.currency', 'program.requisites', 'assignedBy'])
                             ->findOrFail($assignmentId);

            // Obtener aplicación si existe
            $application = Application::where('user_id', $user->id)
                                    ->where('program_id', $assignment->program_id)
                                    ->first();

            return response()->json([
                'success' => true,
                'assignment' => [
                    'id' => $assignment->id,
                    'status' => $assignment->status,
                    'status_name' => $assignment->status_name,
                    'can_apply' => $assignment->can_user_apply,
                    'is_priority' => $assignment->is_priority,
                    'is_overdue' => $assignment->is_overdue,
                    'days_until_deadline' => $assignment->days_until_deadline,
                    'progress_percentage' => $assignment->progress_percentage,
                    'assigned_at' => $assignment->assigned_at->toISOString(),
                    'applied_at' => $assignment->applied_at?->toISOString(),
                    'application_deadline' => $assignment->application_deadline?->toDateString(),
                    'assignment_notes' => $assignment->assignment_notes,
                    'admin_notes' => $assignment->admin_notes,
                    'assigned_by' => [
                        'name' => $assignment->assignedBy->name,
                        'email' => $assignment->assignedBy->email,
                    ],
                    'program' => [
                        'id' => $assignment->program->id,
                        'name' => $assignment->program->name,
                        'description' => $assignment->program->description,
                        'country' => $assignment->program->country,
                        'category' => $assignment->program->category,
                        'location' => $assignment->program->location,
                        'start_date' => $assignment->program->start_date?->toDateString(),
                        'end_date' => $assignment->program->end_date?->toDateString(),
                        'application_deadline' => $assignment->program->application_deadline?->toDateString(),
                        'duration' => $assignment->program->duration,
                        'credits' => $assignment->program->credits,
                        'capacity' => $assignment->program->capacity,
                        'available_spots' => $assignment->program->getAvailableSpots(),
                        'image_url' => $assignment->program->image_url,
                        'cost_display' => 'A Cotizar',
                        'requisites' => $assignment->program->requisites->map(function ($req) {
                            return [
                                'id' => $req->id,
                                'title' => $req->title,
                                'description' => $req->description,
                                'is_required' => $req->is_required,
                                'order' => $req->order,
                            ];
                        }),
                    ],
                    'application' => $application ? [
                        'id' => $application->id,
                        'status' => $application->status,
                        'submitted_at' => $application->created_at->toISOString(),
                        'documents_count' => $application->documents()->count(),
                    ] : null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles de la asignación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply to an assigned program
     */
    public function apply(Request $request, $assignmentId)
    {
        try {
            $user = Auth::user();
            
            $assignment = $user->programAssignments()->findOrFail($assignmentId);

            // Verificar que se puede aplicar
            if (!$assignment->canBeAppliedTo()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes aplicar a este programa en este momento.',
                ], 400);
            }

            // Verificar que no existe una aplicación previa
            $existingApplication = Application::where('user_id', $user->id)
                                             ->where('program_id', $assignment->program_id)
                                             ->first();

            if ($existingApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes una aplicación para este programa.',
                ], 400);
                    }

            DB::beginTransaction();

            // Crear la aplicación
            $application = Application::create([
                'user_id' => $user->id,
                'program_id' => $assignment->program_id,
                'status' => 'submitted',
                'application_data' => $request->get('application_data', []),
            ]);

            // Marcar asignación como aplicada
            $assignment->markAsApplied();

            // Crear notificación para admins
            $this->notifyAdminsOfNewApplication($assignment, $application);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aplicación enviada exitosamente.',
                'application' => [
                    'id' => $application->id,
                    'status' => $application->status,
                    'submitted_at' => $application->created_at->toISOString(),
                ],
                'assignment' => [
                    'id' => $assignment->id,
                    'status' => $assignment->status,
                    'status_name' => $assignment->status_name,
                    'applied_at' => $assignment->applied_at->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar aplicación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get program details for an assignment
     */
    public function getProgramDetails($assignmentId)
    {
        try {
            $user = Auth::user();
            
            $assignment = $user->programAssignments()
                             ->with(['program', 'program.forms', 'program.requisites'])
                             ->findOrFail($assignmentId);

            $program = $assignment->program;

            return response()->json([
                'success' => true,
                'program' => [
                    'id' => $program->id,
                    'name' => $program->name,
                    'description' => $program->description,
                    'country' => $program->country,
                    'category' => $program->category,
                    'location' => $program->location,
                    'start_date' => $program->start_date?->toDateString(),
                    'end_date' => $program->end_date?->toDateString(),
                    'application_deadline' => $program->application_deadline?->toDateString(),
                    'duration' => $program->duration,
                    'credits' => $program->credits,
                    'capacity' => $program->capacity,
                    'available_spots' => $program->getAvailableSpots(),
                    'image_url' => $program->image_url,
                    'cost_display' => 'A Cotizar',
                    'requisites' => $program->requisites->map(function ($req) {
                        return [
                            'id' => $req->id,
                            'title' => $req->title,
                            'description' => $req->description,
                            'is_required' => $req->is_required,
                            'order' => $req->order,
                        ];
                    }),
                    'has_forms' => $program->forms()->where('is_active', true)->exists(),
                    'active_form' => $program->activeForm ? [
                        'id' => $program->activeForm->id,
                        'title' => $program->activeForm->title,
                        'description' => $program->activeForm->description,
                    ] : null,
                ],
                'assignment_status' => $assignment->status,
                'can_apply' => $assignment->canBeAppliedTo(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles del programa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available programs for application (programs assigned to user)
     */
    public function getAvailablePrograms()
    {
        try {
            $user = Auth::user();
            
            $assignments = $user->programAssignments()
                               ->with(['program'])
                               ->canApply()
                               ->get();

            $programs = $assignments->map(function ($assignment) {
                return [
                    'assignment_id' => $assignment->id,
                    'program' => [
                        'id' => $assignment->program->id,
                        'name' => $assignment->program->name,
                        'description' => $assignment->program->description,
                        'country' => $assignment->program->country,
                        'category' => $assignment->program->category,
                        'image_url' => $assignment->program->image_url,
                        'cost_display' => 'A Cotizar',
                    ],
                    'deadline' => $assignment->application_deadline?->toDateString(),
                    'is_priority' => $assignment->is_priority,
                    'days_until_deadline' => $assignment->days_until_deadline,
                ];
            });

            return response()->json([
                'success' => true,
                'programs' => $programs,
                'total' => $programs->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener programas disponibles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get assignment statistics for user
     */
    public function getMyStats()
    {
        try {
            $user = Auth::user();
            
            $assignments = $user->programAssignments();
            
            $stats = [
                'total_assignments' => $assignments->count(),
                'assigned' => $assignments->where('status', ProgramAssignment::STATUS_ASSIGNED)->count(),
                'applied' => $assignments->where('status', ProgramAssignment::STATUS_APPLIED)->count(),
                'under_review' => $assignments->where('status', ProgramAssignment::STATUS_UNDER_REVIEW)->count(),
                'accepted' => $assignments->where('status', ProgramAssignment::STATUS_ACCEPTED)->count(),
                'rejected' => $assignments->where('status', ProgramAssignment::STATUS_REJECTED)->count(),
                'completed' => $assignments->where('status', ProgramAssignment::STATUS_COMPLETED)->count(),
                'active_assignments' => $assignments->active()->count(),
                'pending_applications' => $assignments->canApply()->count(),
                'overdue_applications' => $assignments->where('application_deadline', '<', now())
                                                    ->where('status', ProgramAssignment::STATUS_ASSIGNED)
                                                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Private methods
     */
    private function notifyAdminsOfNewApplication($assignment, $application)
    {
        // Obtener todos los admins
        $admins = \App\Models\User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Nueva Aplicación Recibida',
                'message' => "El usuario {$assignment->user->name} ha aplicado al programa {$assignment->program->name}.",
                'type' => 'new_application',
                'data' => [
                    'assignment_id' => $assignment->id,
                    'application_id' => $application->id,
                    'user_id' => $assignment->user_id,
                    'user_name' => $assignment->user->name,
                    'program_id' => $assignment->program_id,
                    'program_name' => $assignment->program->name,
                ],
                'read_at' => null,
            ]);
        }
    }
}
