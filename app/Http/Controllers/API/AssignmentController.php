<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller para gestionar asignaciones de programas desde la API móvil
 */
class AssignmentController extends Controller
{
    /**
     * Obtener todas las asignaciones del usuario autenticado
     * 
     * GET /api/assignments
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = Assignment::with(['program.currency', 'assignedBy'])
                ->forUser($user->id);
            
            // Filtrar por estado si se proporciona
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }
            
            // Filtrar solo activas si se solicita
            if ($request->boolean('active_only')) {
                $query->active();
            }
            
            $assignments = $query->orderBy('created_at', 'desc')->get();
            
            // Transformar para la respuesta
            $transformedAssignments = $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'status' => $assignment->status,
                    'status_name' => $assignment->status_name,
                    'can_apply' => $assignment->can_apply,
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
                        'category' => $assignment->program->subcategory,
                        'location' => $assignment->program->location,
                        'start_date' => $assignment->program->start_date,
                        'end_date' => $assignment->program->end_date,
                        'duration' => $assignment->program->duration,
                        'image_url' => $assignment->program->image ? asset('storage/' . $assignment->program->image) : null,
                        'cost_display' => $assignment->program->currency 
                            ? $assignment->program->currency->symbol . ' ' . number_format($assignment->program->cost, 2)
                            : '$' . number_format($assignment->program->cost, 2),
                        'available_spots' => $assignment->program->available_slots ?? 0,
                    ],
                    'can_view_details' => $assignment->canViewDetails(),
                    'can_apply_now' => $assignment->canApplyNow(),
                ];
            });
            
            return response()->json([
                'success' => true,
                'assignments' => $transformedAssignments,
                'total' => $transformedAssignments->count(),
                'active_assignments' => $assignments->where('status', 'assigned')->count(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener detalle de una asignación específica
     * 
     * GET /api/assignments/{id}
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            
            $assignment = Assignment::with([
                'program.currency',
                'program.requisites' => function($query) {
                    $query->orderBy('order');
                },
                'assignedBy',
                'application'
            ])
            ->forUser($user->id)
            ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'assignment' => [
                    'id' => $assignment->id,
                    'status' => $assignment->status,
                    'status_name' => $assignment->status_name,
                    'can_apply' => $assignment->can_apply,
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
                        'category' => $assignment->program->subcategory,
                        'location' => $assignment->program->location,
                        'start_date' => $assignment->program->start_date,
                        'end_date' => $assignment->program->end_date,
                        'application_deadline' => $assignment->program->application_deadline,
                        'duration' => $assignment->program->duration,
                        'credits' => $assignment->program->credits,
                        'capacity' => $assignment->program->capacity,
                        'available_spots' => $assignment->program->available_slots ?? 0,
                        'image_url' => $assignment->program->image ? asset('storage/' . $assignment->program->image) : null,
                        'cost_display' => $assignment->program->currency 
                            ? $assignment->program->currency->symbol . ' ' . number_format($assignment->program->cost, 2)
                            : '$' . number_format($assignment->program->cost, 2),
                        'requisites' => $assignment->program->requisites->map(function($req) {
                            return [
                                'id' => $req->id,
                                'title' => $req->name,
                                'description' => $req->description,
                                'is_required' => $req->required,
                                'order' => $req->order ?? 0,
                            ];
                        }),
                    ],
                    'application' => $assignment->application ? [
                        'id' => $assignment->application->id,
                        'status' => $assignment->application->status,
                        'submitted_at' => $assignment->application->applied_at,
                        'documents_count' => $assignment->application->documents()->count(),
                    ] : null,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle de asignación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Aplicar a un programa asignado
     * 
     * POST /api/assignments/{id}/apply
     */
    public function apply(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            $assignment = Assignment::forUser($user->id)->findOrFail($id);
            
            // Verificar que puede aplicar
            if (!$assignment->canApplyNow()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes aplicar a esta asignación en este momento.',
                ], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // Crear la aplicación
                $application = Application::create([
                    'user_id' => $user->id,
                    'program_id' => $assignment->program_id,
                    'status' => 'pending',
                    'applied_at' => now(),
                ]);
                
                // Actualizar la asignación
                $assignment->markAsApplied($application->id);
                
                // Crear registros de requisitos del usuario
                $programRequisites = $assignment->program->requisites;
                foreach ($programRequisites as $requisite) {
                    \App\Models\UserProgramRequisite::create([
                        'application_id' => $application->id,
                        'program_requisite_id' => $requisite->id,
                        'status' => 'pending',
                    ]);
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Aplicación enviada exitosamente',
                    'application' => [
                        'id' => $application->id,
                        'status' => $application->status,
                        'submitted_at' => $application->applied_at->toISOString(),
                    ],
                    'assignment' => [
                        'id' => $assignment->id,
                        'status' => $assignment->status,
                        'status_name' => $assignment->status_name,
                        'applied_at' => $assignment->applied_at->toISOString(),
                    ],
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar aplicación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener detalles del programa de una asignación
     * 
     * GET /api/assignments/{id}/program
     */
    public function getProgramDetails($id)
    {
        try {
            $user = Auth::user();
            
            $assignment = Assignment::with([
                'program.currency',
                'program.requisites' => function($query) {
                    $query->orderBy('order');
                },
                'program.forms' => function($query) {
                    $query->where('is_active', true)->latest();
                }
            ])
            ->forUser($user->id)
            ->findOrFail($id);
            
            $program = $assignment->program;
            $activeForm = $program->forms->first();
            
            return response()->json([
                'success' => true,
                'program' => [
                    'id' => $program->id,
                    'name' => $program->name,
                    'description' => $program->description,
                    'country' => $program->country,
                    'category' => $program->subcategory,
                    'location' => $program->location,
                    'start_date' => $program->start_date,
                    'end_date' => $program->end_date,
                    'application_deadline' => $program->application_deadline,
                    'duration' => $program->duration,
                    'credits' => $program->credits,
                    'capacity' => $program->capacity,
                    'available_spots' => $program->available_slots ?? 0,
                    'image_url' => $program->image ? asset('storage/' . $program->image) : null,
                    'cost_display' => $program->currency 
                        ? $program->currency->symbol . ' ' . number_format($program->cost, 2)
                        : '$' . number_format($program->cost, 2),
                    'requisites' => $program->requisites->map(function($req) {
                        return [
                            'id' => $req->id,
                            'title' => $req->name,
                            'description' => $req->description,
                            'is_required' => $req->required,
                            'order' => $req->order ?? 0,
                        ];
                    }),
                    'has_forms' => $activeForm !== null,
                    'active_form' => $activeForm ? [
                        'id' => $activeForm->id,
                        'title' => $activeForm->title,
                        'description' => $activeForm->description,
                    ] : null,
                ],
                'assignment_status' => $assignment->status,
                'can_apply' => $assignment->canApplyNow(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles del programa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener programas disponibles (asignados y pendientes de aplicar)
     * 
     * GET /api/available-programs
     */
    public function availablePrograms()
    {
        try {
            $user = Auth::user();
            
            $assignments = Assignment::with(['program.currency'])
                ->forUser($user->id)
                ->byStatus('assigned')
                ->orderBy('is_priority', 'desc')
                ->orderBy('application_deadline', 'asc')
                ->get();
            
            $programs = $assignments->map(function ($assignment) {
                return [
                    'assignment_id' => $assignment->id,
                    'program' => [
                        'id' => $assignment->program->id,
                        'name' => $assignment->program->name,
                        'description' => $assignment->program->description,
                        'country' => $assignment->program->country,
                        'category' => $assignment->program->subcategory,
                        'image_url' => $assignment->program->image ? asset('storage/' . $assignment->program->image) : null,
                        'cost_display' => $assignment->program->currency 
                            ? $assignment->program->currency->symbol . ' ' . number_format($assignment->program->cost, 2)
                            : '$' . number_format($assignment->program->cost, 2),
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
     * Obtener estadísticas de asignaciones del usuario
     * 
     * GET /api/my-stats
     */
    public function myStats()
    {
        try {
            $user = Auth::user();
            
            $stats = [
                'total_assignments' => Assignment::forUser($user->id)->count(),
                'assigned' => Assignment::forUser($user->id)->byStatus('assigned')->count(),
                'applied' => Assignment::forUser($user->id)->byStatus('applied')->count(),
                'under_review' => Assignment::forUser($user->id)->byStatus('under_review')->count(),
                'accepted' => Assignment::forUser($user->id)->byStatus('accepted')->count(),
                'rejected' => Assignment::forUser($user->id)->byStatus('rejected')->count(),
                'completed' => Assignment::forUser($user->id)->byStatus('completed')->count(),
                'active_assignments' => Assignment::forUser($user->id)->active()->count(),
                'pending_applications' => Assignment::forUser($user->id)->byStatus('assigned')->count(),
                'overdue_applications' => Assignment::forUser($user->id)->overdue()->count(),
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
}
