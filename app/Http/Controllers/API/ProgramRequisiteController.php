<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\ProgramRequisite;
use App\Models\Application;
use App\Models\UserProgramRequisite;

class ProgramRequisiteController extends Controller
{
    /**
     * Obtiene los requisitos de un programa específico.
     *
     * @param  int  $programId
     * @return \Illuminate\Http\Response
     */
    /**
     * Obtiene los requisitos de un programa específico.
     * GET /api/programs/{programId}/requisites
     */
    public function getProgramRequisites($programId)
    {
        try {
            $program = Program::findOrFail($programId);
            $requisites = $program->requisites()->orderBy('order')->get();
            
            // Transformar para la respuesta
            $transformedRequisites = $requisites->map(function($req) {
                return [
                    'id' => $req->id,
                    'name' => $req->name,
                    'description' => $req->description,
                    'type' => $req->type,
                    'required' => $req->required,
                    'order' => $req->order ?? 0,
                    'payment_amount' => $req->payment_amount,
                    'currency_id' => $req->currency_id,
                    'deadline' => $req->deadline,
                ];
            });
            
            return response()->json([
                'success' => true,
                'requisites' => $transformedRequisites,
                'total' => $transformedRequisites->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener requisitos del programa: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtiene los requisitos de la aplicación de un usuario a un programa.
     *
     * @param  int  $applicationId
     * @return \Illuminate\Http\Response
     */
    /**
     * Obtiene los requisitos de la aplicación de un usuario a un programa.
     * GET /api/applications/{applicationId}/requisites
     */
    public function getUserRequisites($applicationId)
    {
        try {
            $application = Application::where('id', $applicationId)
                ->where('user_id', auth()->id())
                ->firstOrFail();
                
            $requisites = $application->requisites()
                ->with(['programRequisite.currency'])
                ->orderBy('created_at')
                ->get();
            
            // Transformar para la respuesta
            $transformedRequisites = $requisites->map(function($userReq) {
                $programReq = $userReq->programRequisite;
                return [
                    'id' => $userReq->id,
                    'program_requisite_id' => $programReq->id,
                    'name' => $programReq->name,
                    'description' => $programReq->description,
                    'type' => $programReq->type,
                    'required' => $programReq->required,
                    'order' => $programReq->order ?? 0,
                    'status' => $userReq->status,
                    'completed_at' => $userReq->completed_at?->toISOString(),
                    'verified_at' => $userReq->verified_at?->toISOString(),
                    'rejected_at' => $userReq->rejected_at?->toISOString(),
                    'file_path' => $userReq->file_path ? asset('storage/' . $userReq->file_path) : null,
                    'admin_notes' => $userReq->admin_notes,
                    'payment_amount' => $programReq->payment_amount,
                    'currency' => $programReq->currency ? [
                        'code' => $programReq->currency->code,
                        'symbol' => $programReq->currency->symbol,
                    ] : null,
                    'deadline' => $programReq->deadline,
                ];
            });
                
            return response()->json([
                'success' => true,
                'requisites' => $transformedRequisites,
                'total' => $transformedRequisites->count(),
                'progress_percentage' => $application->getProgressPercentage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener requisitos de la aplicación: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Marca un requisito como completado y sube un archivo si es necesario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $requisiteId
     * @return \Illuminate\Http\Response
     */
    /**
     * Marca un requisito como completado y sube un archivo si es necesario.
     * POST /api/requisites/{requisiteId}/complete
     */
    public function completeRequisite(Request $request, $requisiteId)
    {
        try {
            $userRequisite = UserProgramRequisite::where('id', $requisiteId)
                ->whereHas('application', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->with('programRequisite')
                ->firstOrFail();
                
            // Validar que el requisito no esté ya verificado
            if ($userRequisite->status === 'verified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este requisito ya ha sido verificado y no puede ser modificado.'
                ], 422);
            }
            
            // Si es un documento, validar y guardar el archivo
            if ($userRequisite->programRequisite->type === 'document') {
                if (!$request->hasFile('file')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes subir un archivo para completar este requisito.'
                    ], 422);
                }
                
                $request->validate([
                    'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB máximo
                ]);
                
                // Eliminar archivo anterior si existe
                if ($userRequisite->file_path && \Storage::disk('public')->exists($userRequisite->file_path)) {
                    \Storage::disk('public')->delete($userRequisite->file_path);
                }
                
                // Guardar el archivo
                $filePath = $request->file('file')->store('requisites/' . $userRequisite->application_id, 'public');
                $userRequisite->file_path = $filePath;
            }
            
            // Validar notas si se proporcionan
            if ($request->has('notes')) {
                $request->validate([
                    'notes' => 'nullable|string|max:1000',
                ]);
                $userRequisite->user_notes = $request->notes;
            }
            
            // Actualizar el estado del requisito
            $userRequisite->status = 'completed';
            $userRequisite->completed_at = now();
            $userRequisite->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Requisito completado correctamente. Será revisado por un administrador.',
                'requisite' => [
                    'id' => $userRequisite->id,
                    'status' => $userRequisite->status,
                    'completed_at' => $userRequisite->completed_at->toISOString(),
                    'file_path' => $userRequisite->file_path ? asset('storage/' . $userRequisite->file_path) : null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar requisito: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtiene el progreso general de la aplicación de un usuario.
     *
     * @param  int  $applicationId
     * @return \Illuminate\Http\Response
     */
    /**
     * Obtiene el progreso general de la aplicación de un usuario.
     * GET /api/applications/{applicationId}/progress
     */
    public function getApplicationProgress($applicationId)
    {
        try {
            $application = Application::where('id', $applicationId)
                ->where('user_id', auth()->id())
                ->with('program')
                ->firstOrFail();
                
            $totalRequisites = $application->requisites()->count();
            $completedRequisites = $application->requisites()
                ->where('status', 'completed')
                ->count();
            $verifiedRequisites = $application->requisites()
                ->where('status', 'verified')
                ->count();
            $pendingRequisites = $application->requisites()
                ->where('status', 'pending')
                ->count();
            $rejectedRequisites = $application->requisites()
                ->where('status', 'rejected')
                ->count();
                
            $progressPercentage = $application->getProgressPercentage();
            
            return response()->json([
                'success' => true,
                'progress' => [
                    'total_requisites' => $totalRequisites,
                    'completed' => $completedRequisites,
                    'verified' => $verifiedRequisites,
                    'pending' => $pendingRequisites,
                    'rejected' => $rejectedRequisites,
                    'progress_percentage' => $progressPercentage,
                    'is_complete' => $progressPercentage === 100,
                ],
                'application' => [
                    'id' => $application->id,
                    'status' => $application->status,
                    'program_name' => $application->program->name,
                    'applied_at' => $application->applied_at?->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener progreso de la aplicación: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtiene un requisito específico del usuario.
     * GET /api/requisites/{requisiteId}
     */
    public function getRequisite($requisiteId)
    {
        try {
            $userRequisite = UserProgramRequisite::where('id', $requisiteId)
                ->whereHas('application', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->with(['programRequisite.currency', 'application.program'])
                ->firstOrFail();
            
            $programReq = $userRequisite->programRequisite;
            
            return response()->json([
                'success' => true,
                'requisite' => [
                    'id' => $userRequisite->id,
                    'program_requisite_id' => $programReq->id,
                    'name' => $programReq->name,
                    'description' => $programReq->description,
                    'type' => $programReq->type,
                    'required' => $programReq->required,
                    'status' => $userRequisite->status,
                    'completed_at' => $userRequisite->completed_at?->toISOString(),
                    'verified_at' => $userRequisite->verified_at?->toISOString(),
                    'rejected_at' => $userRequisite->rejected_at?->toISOString(),
                    'file_path' => $userRequisite->file_path ? asset('storage/' . $userRequisite->file_path) : null,
                    'user_notes' => $userRequisite->user_notes,
                    'admin_notes' => $userRequisite->admin_notes,
                    'payment_amount' => $programReq->payment_amount,
                    'currency' => $programReq->currency ? [
                        'code' => $programReq->currency->code,
                        'symbol' => $programReq->currency->symbol,
                    ] : null,
                    'deadline' => $programReq->deadline,
                    'program' => [
                        'id' => $userRequisite->application->program->id,
                        'name' => $userRequisite->application->program->name,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener requisito: ' . $e->getMessage(),
            ], 500);
        }
    }
}
